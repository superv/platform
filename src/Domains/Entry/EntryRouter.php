<?php namespace SuperV\Platform\Domains\Entry;

use Illuminate\Routing\UrlGenerator;

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

    public function __construct(EntryModel $entry, UrlGenerator $url)
    {
        $this->url = $url;
        $this->entry = $entry;
    }

    public function make($route, array $parameters = [])
    {
        if (method_exists($this, $method = camel_case(str_replace('.', '_', $route)))) {

            $parameters['parameters'] = $parameters;

            return app()->call([$this, $method], $parameters);
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