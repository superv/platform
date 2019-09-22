<?php

namespace SuperV\Platform\Domains\Resource;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function createForm()
    {
        return sprintf(
            sv_route('sv::forms.show', [
                'identifier' => $this->resource->getIdentifier().'.forms.default',
            ])
        );
    }

    public function defaultList()
    {
        return sprintf(
            sv_route('resource.table', [
                'resource' => $this->resource->getIdentifier(),
            ])
        );
    }

    public function dashboard()
    {
        return sprintf(
            sv_route('resource.dashboard', [
                'resource' => $this->resource->getIdentifier(),
            ])
        );
    }
}
