<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Current;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends FieldComposer
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType */
    protected $fieldType;

    public function table(?EntryContract $entry = null): void
    {
        if ($entry) {
            $this->payload->set('value', $this->fieldType->getRelatedEntry($entry)->getEntryLabel());
        }
    }

    public function view(EntryContract $entry): void
    {
        $relatedEntry = $this->fieldType->getRelatedEntry($entry);

        $this->payload->set('value', $relatedEntry->getEntryLabel());

        if (Current::user()->can($this->field->getConfigValue('related'))) {
            $this->payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
        }
    }

    public function form(?FormInterface $form = null): void
    {
        $options = $this->field->getConfigValue('meta.options');
        if (! is_null($options)) {
            $this->getPayload()->set('meta.options', $options);
        } else {
            $url = sv_route('sv::forms.field_rpc', [
                'form'  => $form->getIdentifier(),
                'field' => $this->field->getHandle(),
                'rpc'   => 'options',
            ]);
            $this->getPayload()->set('meta.options', $url);
        }
        $this->getPayload()->set('placeholder', __('Select :Object', [
            'object' =>  $this->field->type()->getRelated()->getSingularLabel(),
        ]));


    }
}