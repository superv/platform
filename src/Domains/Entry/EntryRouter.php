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

    public function make($route, array $parameters = [])
    {
        if (method_exists($this, $method = camel_case(str_replace('.', '_', $route)))) {

            $parameters['parameters'] = $parameters;

            return app()->call([$this, $method], $parameters);
        }

        if ($manifest = $this->manifests->byModel(get_class($this->entry))) {
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