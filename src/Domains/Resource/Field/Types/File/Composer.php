<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Types\File\FileType */
    protected $fieldType;

    public function view(EntryContract $entry): void
    {
        if ($media = $this->fieldType->getMedia($entry, $this->getFieldHandle())) {
            $this->payload->set('image_url', $media->getUrl());
            $this->payload->set('config', null);
            $this->payload->set('value', null);
        }
    }

    public function table(?EntryContract $entry = null): void
    {
        if ($entry) {
            if ($media = FileType::getMedia($entry, $this->field->getHandle())) {
                $this->payload->set('image_url', $media->getUrl());
                $this->payload->set('config', null);
            }
        }
    }

    public function form(?FormInterface $form = null): void
    {
        if (! $form->hasEntry()) {
            return;
        }

        if ($media = FileType::getMedia($form->getEntry(), $this->field->getHandle())) {
            $this->payload->set('image_url', $media->getUrl());
            $this->payload->set('config', null);
        }
    }
}