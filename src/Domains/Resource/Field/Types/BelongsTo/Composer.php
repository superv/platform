<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;

use Current;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function table(?EntryContract $entry = null): void
    {
        if ($entry) {
            if ($callback = $this->field->getCallback('table.composing')) {
                app()->call($callback, ['entry' => $entry, 'payload' => $this->payload]);
            }
        }
    }

    public function view(EntryContract $entry): void
    {
        $relatedEntry = $entry->{$this->getFieldHandle()}()->newQuery()->first();

        if ($relatedEntry) {
            $this->getPayload()->set('value', sv_resource($relatedEntry)->getEntryLabel($relatedEntry));
            if (Current::user()->can($this->field->getConfigValue('related'))) {
                $this->getPayload()->set('meta.link', $relatedEntry->router()->dashboardSPA());
            }
        }
    }

    public function form(?FormInterface $form = null): void
    {
        $entry = $form->getEntry();

        if ($entry) {
            if ($relatedEntry = $entry->{$this->field->getHandle()}()->newQuery()->first()) {
                $this->getPayload()->set('meta.link', $relatedEntry->router()->dashboardSPA());
            }
        }

        $options = $this->field->getConfigValue('meta.options');
        if (! is_null($options)) {
            $this->getPayload()->set('meta.options', $options);
        } else {
            $url = sv_route('sv::forms.fields', [
                'form'  => $form->getIdentifier(),
                'field' => $this->field->getHandle(),
                'rpc'   => 'options',
            ]);
            $this->getPayload()->set('meta.options', $url);
        }
        $this->getPayload()->set('placeholder', __('Select :Object', [
            'object' => $this->fieldType->getRelatedResource()->getSingularLabel(),
        ]));
    }
}