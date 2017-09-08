<?php

namespace SuperV\Platform\Domains\Asset;

use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use SuperV\Platform\Contracts\Filesystem;

class Asset
{
    private $collections = [];

    /**
     * @var \Collective\Html\HtmlBuilder
     */
    private $html;

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

    public function __construct(Filesystem $files, UrlGenerator $url, Request $request)
    {
        $this->request = $request;
        $this->files = $files;
        $this->url = $url;
    }

    public function add($collection, $file, array $filters = [])
    {
        if (! isset($this->collections[$collection])) {
            $this->collections[$collection] = [];
        }

//        $filters = $this->addConvenientFilters($file, $filters);

//        $file = $this->paths->realPath($file);
        $file = base_path($file);

        if (count(glob($file)) > 0) {
            $this->collections[$collection][$file] = array_merge($filters, ['glob']);

            return $this;
        }

        throw new \Exception("Asset [{$file}] does not exist!");
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

    public function style($collection, array $filters = [], array $attributes = [])
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

        $attributes = $attributes + $defaults;

        $attributes['href'] = $this->path($collection, $filters);

        return '<link'.html_attributes($attributes).'>';
    }

    public function path($collection, array $filters = [])
    {
        $basePath = $this->request->getBasePath();

        return $basePath.$this->getPath($collection, $filters);
    }

    protected function getPath($collection, array $filters = [])
    {
        $path = "/app/assets/supreme/{$collection}";

        if ($this->shouldPublish($path, $collection, $filters)) {
            \Log::info('publishing '.$collection);
            $this->publish($path, $collection, $filters);
        }

        return $path;
    }

    protected function publish($path, $collection, $additionalFilters)
    {
        $path = ltrim($path, '/\\');

        $assets = $this->getAssetCollection($collection, $additionalFilters);

        $path = public_path($path);

        $this->files->makeDirectory((new \SplFileInfo($path))->getPath(), 0777, true, true);

        $this->files->put($path, $assets->dump());
    }

    protected function shouldPublish($path, $collection, array $filters = [])
    {
        return true;
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

        $debug = config('superv.assets.live', false);

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
}
