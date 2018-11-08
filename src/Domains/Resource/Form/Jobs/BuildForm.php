<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Dispatchable;

class BuildForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function handle()
    {
        if ($this->form->isBuilt()) {
            throw new PlatformException('Form is already built.');
        }

        // first add all fields from all resources
        $this->form->getResources()->map(function (Resource $resource) {
            $resource->build();

            $resource->copyFreshFields()
                     ->map(function (Field $field) {
                         $this->form->addField($field);
                     });
        });
        $this->form->fire('building.fields', ['form' => $this->form]);
        $this->form->getFields()->map->build();
    }
}