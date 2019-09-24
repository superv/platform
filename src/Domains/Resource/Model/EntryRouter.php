<?php

namespace SuperV\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class EntryRouter
{
    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $entry;

    public function __construct(EntryContract $entry)
    {
        $this->entry = $entry;
    }

    public function updateForm()
    {
        return sprintf(
            sv_route('sv::forms.display', [
                'identifier' => $this->entry->getResourceIdentifier().'.forms:default',
                'entry'      => $this->entry->getId(),
            ])
        );
    }

    public function view()
    {
        return sprintf(
            sv_route('resource.entry.view', [
                'resource' => $this->entry->getResourceIdentifier(),
                'id'       => $this->entry->getId(),
            ])
        );
    }

    public function dashboard($section = null)
    {
        return sprintf(
            sv_route('resource.entry.dashboard', [
                'resource' => $this->entry->getResourceIdentifier(),
                'id'       => $this->entry->getId(),
                'section'  => $section,
            ])
        );
    }

    public function dashboardSPA($section = null)
    {
        return sprintf(
            route('resource.entry.dashboard', [
                'resource' => $this->entry->getResourceIdentifier(),
                'id'       => $this->entry->getId(),
                'section'  => $section,
            ], false)
        );
    }
}
