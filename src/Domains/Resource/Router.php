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
                'namespace' => $this->resource->getIdentifier(),
                'name'      => 'default',
            ])
        );
    }
}
