<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Nucleo\Nucleo;

/**
 * Class ResourceController
 *
 * @package Nucleo
 */
class ResourceController extends BaseController
{
    /** @var \SuperV\Platform\Domains\Nucleo\Resource\Resource */
    protected $resource;

    protected function resource()
    {
        $slug = $this->route->parameter('resource');
        if (! $this->resource = Nucleo::resourceBySlug($slug)) {
            throw new \Exception('Resource needed here...');
        }

        return app($this->resource)->build();
    }

    public function index()
    {
        return $this->resource()->getIndex()->render();
    }

    public function editor()
    {
        return $this->resource()->getEditor()->render();
    }

    public function show($resource, $id)
    {
        $entry = $this->resource()->load($id)->entry();

        return ['data' => ['state' => $entry->compose()]];
    }

    public function store()
    {
        $entry =  $this->resource()->create()->entry();

        return ['data' => ['state' => $entry->compose()]];
    }

    public function update($resource, $id)
    {
        $entry = $this->resource()->load($id)->update()->entry();

        return ['data' => ['state' => $entry->compose()]];
    }

    public function delete($resource, $id)
    {
        $this->resource()->load($id)->delete();
    }
}