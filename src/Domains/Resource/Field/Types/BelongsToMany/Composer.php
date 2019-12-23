<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsToMany;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function form(?FormInterface $form = null): void
    {
        $relatedResource = $this->fieldType->resolveRelatedResource();

        if ($form && $entry = $form->getEntry()) {
            if ($relatedEntry = $entry->{$this->getFieldHandle()}()->newQuery()->first()) {
                $this->payload->set('meta.link', $relatedEntry->router()->view());
            }
        }

        $this->payload->set('meta.options', $this->getOptionsUrl($entry));

        if ($entry && $entry->exists) {
            $this->payload->set('meta.values', $this->getValuesUrl($entry));
        }
        $this->payload->set('meta.full', true);
        $this->payload->set('placeholder', 'Select '.$relatedResource->getSingularLabel());
    }

    public function getOptionsUrl(?EntryContract $entry = null)
    {
        $url = sv_route('sv::forms.fields', [
            'form'  => $this->field->getForm()->getIdentifier(),
            'field' => $this->getFieldHandle(),
            'rpc'   => 'options',
        ]);
        if ($entry && $entry->exists) {
            $url .= '?entry='.$entry->getId();
        }

        return $url;
    }

    public function getValuesUrl(?EntryContract $entry = null)
    {
        $url = sv_route('sv::forms.fields', [
            'form'  => $this->field->getForm()->getIdentifier(),
            'field' => $this->getFieldHandle(),
            'rpc'   => 'values',
        ]);
        if ($entry && $entry->exists) {
            $url .= '?entry='.$entry->getId();
        }

        return $url;
    }
}