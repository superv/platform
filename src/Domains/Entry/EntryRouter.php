<?php namespace SuperV\Platform\Domains\Entry;

use Illuminate\Routing\UrlGenerator;
use SuperV\Platform\Domains\Manifest\ManifestCollection;

class EntryRouter
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var EntryModel
     */
    protected $entry;

    /**
     * @var ManifestCollection
     */
    private $manifests;

    public function __construct(EntryModel $entry, UrlGenerator $url, ManifestCollection $manifests)
    {
        $this->url = $url;
        $this->entry = $entry;
        $this->manifests = $manifests;
    }

    public function delete()
    {
        $config = ['class' => get_class($this->entry), 'id' => $this->entry->getId()];
        $ticket = md5(json_encode($config));

        superv('cache')->remember(
            'superv::platform.tickets:' . $ticket,
            3600,
            function () use($config) {
                return $config;
            }
        );

        return $this->url->route('superv::entries.delete', ['ticket' => $ticket]);
    }

    public function edit()
    {
        $config = ['class' => get_class($this->entry), 'id' => $this->entry->getId()];
        $ticket = md5(json_encode($config));

        superv('cache')->remember(
            'superv::platform.tickets:' . $ticket,
            3600,
            function () use($config) {
                return $config;
            }
        );

        return $this->url->route('superv::entries.edit', ['ticket' => $ticket]);
    }

    public function make($route, array $parameters = [])
    {
        if (method_exists($this, $method = camel_case(str_replace('.', '_', $route)))) {

            $parameters['parameters'] = $parameters;

            return app()->call([$this, $method], $parameters);
        }

        /**
         * If this model has a manifest and this
         * route is defined there, return route
         * info from the manifest data
         */
        if ($manifest = $this->manifests->model()->byModel(get_class($this->entry))) {
            if ($pages = $manifest->getPages()) {
                if ($page = array_get($pages, $route)) {
                    if ($pageRoute = array_get($page, 'route')) {
                        return $this->url->route($pageRoute, ['id' => $this->entry->id]);
                    }
                }
            }
        }

//
//        if (!str_contains($route, '.') && $stream = $this->entry->getStreamSlug()) {
//            $route = "{$stream}.{$route}";
//        }
//
//        if (!str_contains($route, '::') && $namespace = $this->locator->locate($this->entry)) {
//            $route = "{$namespace}::{$route}";
//        }
//
//        return $this->url->make($route, $this->entry, $parameters);
    }
}