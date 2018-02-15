<?php

namespace SuperV\Platform\Domains\Asset;

use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use SuperV\Platform\Domains\Droplet\DropletModel;

class Asset
{
    private $collections = [];

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \SuperV\Platform\Contracts\Filesystem
     */
    private $files;

    /**
     * @var \Illuminate\Routing\UrlGenerator
     */
    private $url;

    protected $paths = [];

    public function __construct(Filesystem $files, UrlGenerator $url, Request $request)
    {
        $this->request = $request;
        $this->files = $files;
        $this->url = $url;
    }

    public function url($collection, array $filters = [], array $parameters = [], $secure = null)
    {
        if (! isset($this->collections[$collection])) {
            $this->add($collection, $collection, $filters);
        }

        if (! $path = $this->getPath($collection, $filters)) {
            return null;
        }

        return $this->url->asset($this->getPath($collection, $filters), $parameters, $secure);
    }

    public function script($collection, array $filters = [], array $attributes = [])
    {
        if (! array_has($this->collections, $collection)) {
            return null;
        }

        $attributes['src'] = $this->path($collection, $filters);

        return '<script'.html_attributes($attributes).'></script>';
    }

    public function style($collection, array $filters = [], array $attributes = [])
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

        $attributes = $attributes + $defaults;

        $attributes['href'] = $this->path($collection, $filters);

        return '<link'.html_attributes($attributes).'>';
    }

    public function styles($collection, array $filters = [], array $attributes = [])
    {
        return array_map(
            function ($path) use ($attributes) {
                $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

                $attributes = $attributes + $defaults;

                $attributes['href'] = $path;

                return '<link'.html_attributes($attributes).'>';
            },
            $this->paths($collection, $filters)
        );
    }


    public function add($collection, $file, array $filters = [])
    {
        if (! isset($this->collections[$collection])) {
            $this->collections[$collection] = [];
        }

        $file = $this->realPath($file);

        if (count(glob($file)) > 0) {
            $this->collections[$collection][$file] = array_merge($filters, ['glob']);

            return $this;
        }

        throw new \Exception("Asset [{$file}] does not exist!");
    }

    public function path($collection, array $filters = [])
    {
        if (! isset($this->collections[$collection])) {
            $this->add($collection, $collection, $filters);
        }

        return $this->request->getBasePath().$this->getPath($collection, $filters);
    }

    public function paths($collection, array $additionalFilters = [])
    {
        if (! isset($this->collections[$collection])) {
            return [];
        }

        return array_filter(
            array_map(
                function ($file, $filters) use ($additionalFilters) {
                    $filters = array_filter(array_unique(array_merge($filters, $additionalFilters)));

                    return $this->asset($file, $filters);
                },
                array_keys($this->collections[$collection]),
                array_values($this->collections[$collection])
            )
        );
    }

    public function asset($collection, array $filters = [])
    {
        if (! isset($this->collections[$collection])) {
            $this->add($collection, $collection, $filters);
        }

        return $this->path($collection, $filters);
    }

    protected function getPath($collection, array $filters = [])
    {
        $path = "/app/assets/{$collection}";

        if ($this->shouldPublish($path, $collection, $filters)) {
            $this->publish($path, $collection, $filters);
        }

        $path .= '?v=' . filemtime(public_path(trim($path, '/\\')));

        return $path;
    }

    protected function publish($path, $collection, $additionalFilters)
    {
        $path = ltrim($path, '/\\');

        $assets = $this->getAssetCollection($collection, $additionalFilters);

        $path = public_path($path);

        $this->files->makeDirectory((new \SplFileInfo($path))->getPath(), 0777, true, true);

        $contents = $assets->dump();

        $hint = pathinfo($collection, PATHINFO_EXTENSION);

        if ($hint == 'css') {
                 try {
                     $contents = app(Template::class)
                         ->render($contents)
                         ->render();
                 } catch (\Exception $e) {
                     if (env('APP_DEBUG')) {
                         dd($e->getMessage());
                     }
                 }
             }


        $this->files->put($path, $contents);
    }

    protected function shouldPublish($path, $collection, array $filters = [])
    {
        $path = ltrim($path, '/\\');

        if (starts_with($path, 'http')) {
            return false;
        }

        if (! $this->files->exists($path)) {
            return true;
        }

        if (in_array('force', $this->collectionFilters($collection, $filters))) {
            return true;
        }

        $debug = \Platform::config('assets.live', true);

        if ($debug) return true;

        $live = in_array('live', $this->collectionFilters($collection, $filters));

        if ($debug === true && $live) {
            return true;
        }

        // Merge filters from collection files.
        foreach ($this->collections[$collection] as $fileFilters) {
            $filters = array_filter(array_unique(array_merge($filters, $fileFilters)));
        }

        $assets = $this->getAssetCollection($collection);

        // If any of the files are more recent than the cache file, publish, otherwise skip
        if ($assets->getLastModified() < filemtime($path)) {
            return false;
        }

        return true;
    }

    protected function collectionFilters($collection, array $filters = [])
    {
        return array_unique(
            array_merge($filters, call_user_func_array('array_merge', array_get($this->collections, $collection, [])))
        );
    }

    protected function getAssetCollection($collection, $additionalFilters = [])
    {
        $assets = new AssetCollection();

        $hint = pathinfo($collection, PATHINFO_EXTENSION);

        foreach ($this->collections[$collection] as $file => $filters) {
            $filters = array_filter(array_unique(array_merge($filters, $additionalFilters)));

            if (in_array('glob', $filters)) {
                unset($filters[array_search('glob', $filters)]);

                $file = new GlobAsset($file, $filters);
            } else {
                $file = new FileAsset($file, $filters);
            }

            $assets->add($file);
        }

        return $assets;
    }

    /**
     * Return nothing.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    public function addPath($hint, $location)
    {
        $this->paths[$hint] = $location;
    }

    protected function realPath($file)
    {
        if (str_is('*::*', $file)) {
            list($slug, $file) = explode('::', $file);

            if ($path = array_get($this->paths, $slug)) {
                $file = $this->paths['theme'].DIRECTORY_SEPARATOR.$file;
            } else {
                if (!$droplet = DropletModel::bySlug($slug)) {
                    throw new \Exception("Asset Droplet not found: {$slug}");
                }
                $file = $droplet->path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$file;
            }
        }

        return base_path($file);
    }
}