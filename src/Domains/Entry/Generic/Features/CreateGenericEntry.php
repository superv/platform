<?php

namespace SuperV\Platform\Domains\Entry\Generic\Features;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Entry\Generic\GenericEntryModel;
use SuperV\Platform\Domains\Entry\Generic\GenericModelModel;
use SuperV\Platform\Domains\Feature\Feature;

class CreateGenericEntry extends Feature
{
    /**
     * @var EntryModel
     */
    private $entry;

    public function __construct(EntryModel $entry)
    {
        $this->entry = $entry;
    }

    public function handle(GenericModelModel $genericModel, GenericEntryModel $genericEntry)
    {
        if ($this->entry instanceof GenericEntryModel || $this->entry instanceof GenericModelModel) {
            return;
        }

        $modelEntry = $genericModel->firstOrCreate([
            'model' => get_class($this->entry),
            'slug'  => $this->entry->getSlug(),
        ]);

        $genericEntry->create(
            [
                'model_id' => $modelEntry->id,
                'link_id'  => $this->entry->getId(),
            ]
        );
    }
}