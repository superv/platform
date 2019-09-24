<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

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
            sv_route('sv::forms.display', [
                'form' => $this->resource->getIdentifier().'.forms:default',
            ])
        );
    }

    public function updateForm(EntryContract $entry)
    {
        return sprintf(
            sv_route('sv::forms.display', [
                'identifier' => $this->resource->getIdentifier().'.forms:default',
                'entry'      => $entry->getId(),
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

    public function entryView(EntryContract $entry)
    {
        return sprintf(
            sv_route('resource.entry.view', [
                'resource' => $this->resource->getIdentifier(),
                'entry'    => $entry->getId(),
            ])
        );
    }

    public function dashboard($section = null)
    {
        return sprintf(
            sv_route('resource.dashboard', [
                'resource' => $this->resource->getIdentifier(),
                'section'  => $section,
            ])
        );
    }

    public function dashboardSPA($section = null)
    {
        return sprintf(
            route('resource.dashboard', [
                'resource' => $this->resource->getIdentifier(),
                'section'  => $section,
            ], false)
        );
    }
}
