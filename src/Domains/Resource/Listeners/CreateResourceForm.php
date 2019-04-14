<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class CreateResourceForm
{
    /** @var string */
    protected $table;

    /** @var \SuperV\Platform\Domains\Resource\Form\FormModel */
    protected $formEntry;

    public function handle(TableCreatedEvent $event)
    {
        if (starts_with($event->table, 'sv_')) {
            return;
        }

        $resource = ResourceFactory::make($event->table);

        $this->formEntry = app(FormFactory::class)->create([
            'resource_id' => $resource->id(),
            'title'       => $resource->getLabel(). ' Form',
        ]);

        $resource->getFieldEntries()
                 ->map(function (FieldModel $field) {
                     $this->formEntry->fields()->attach($field->getId());
                 });
    }
}