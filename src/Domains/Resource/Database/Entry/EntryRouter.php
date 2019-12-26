<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

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

    public function delete()
    {
        return sv_route('resource.entry.delete', [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
        ]);
    }

    public function restore()
    {
        return sv_route('resource.entry.restore', [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
        ]);
    }

    public function actions($action)
    {
        return sv_route('resource.entry.actions', [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
            'action'   => $action,
        ]);
    }

    public function fields($field)
    {
        return sv_route('resource.entry.fields', [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
            'field'    => $field,
        ]);
    }

    public function fieldAction($field, $action)
    {
        return sv_route('resource.entry.field_actions.'.$action, [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
            'field'    => $field,
        ]);
    }

    public function updateForm()
    {
        return sv_route('sv::forms.display', [
            'form'  => $this->entry->getResourceIdentifier().'.forms:default',
            'entry' => $this->entry->getId(),
        ]);
    }

    public function update()
    {
        return sv_route('sv::forms.submit', [
            'form'  => $this->entry->getResourceIdentifier().'.forms:default',
            'entry' => $this->entry->getId(),
        ]);
    }

    public function view()
    {
        return sv_route('sv::entry.view', [
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
        ]);
    }

    public function dashboard($section = null)
    {
        return sv_route('resource.entry.dashboard', array_filter([
            'resource' => $this->entry->getResourceIdentifier(),
            'entry'    => $this->entry->getId(),
            'section'  => $section,
        ]));
    }

    public function dashboardSPA($section = null)
    {
        return route('resource.entry.dashboard', array_filter(
            [
                'resource' => $this->entry->getResourceIdentifier(),
                'entry'    => $this->entry->getId(),
                'section'  => $section,
            ]
        ), false);
    }
}
