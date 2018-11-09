<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Dispatchable;

class BuildFormDeprecated
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $resources;

    public function __construct(Form $form, Collection $resources)
    {
        $this->form = $form;
        $this->resources = $resources;
    }

    public function handle()
    {
        if ($this->form->isBuilt()) {
            throw new PlatformException('Form is already built.');
        }

        // first add all fields from all resources
        $this->resources->map(function (Resource $resource) {
//            $this->form->addResource($resource);
            $resource->build();

            $this->form->addEntry($entry = new Entry($resource->getEntry()));

            $resource->copyFreshFields()
                     ->map(function (FieldType $field) use ($entry) {
                         $field->setEntry($entry);
                         $this->form->addField($field);
                     });
        });

        $this->form->fire('building.fields', ['form' => $this->form]);
        $this->form->getFields()->map->build();

        $this->form->setBuilt(true);

        $this->form->cache();
    }
}