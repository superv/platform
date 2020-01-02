<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Actions;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class LookupAction extends BaseAction
{
    protected $name = 'attach';

    protected $title = 'Attach New';

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-attach-action')
                     ->setProp('modal-size', 'w-5/6');
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('lookup-url', $this->getLookupUrl());
        $payload->set('attach-url', $this->getAttachUrl());
//        $payload->set('pivot-fields', $this->getPivotFormFields());
    }

    public function getLookupUrl()
    {
        return $this->field->router()->route('lookup').'?'.http_build_query([
                'entry' => $this->parentEntry->getId(),
                'field' => $this->field->getIdentifier(),
            ]);
    }

    public function getAttachUrl()
    {
        return $this->field->router()->route('attach').'?'.http_build_query([
                'entry' => $this->parentEntry->getId(),
                'field' => $this->field->getIdentifier(),
            ]);
    }

    protected function getPivotFormFields()
    {
        if ($pivotColumns = $this->relation->getRelationConfig()->getPivotColumns()) {
            return $this->relation->getPivotFields()->map(function (FieldInterface $field) {
//                return (new FieldComposer($field))->forForm();
                return $field->getComposer()->toForm();
            });
        }
    }
}