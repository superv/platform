<?php

namespace SuperV\Platform\Domains\Resource\Relation\Actions;

use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class LookupAttachablesAction extends Action
{
    protected $name = 'attach';

    protected $title = 'Attach New';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-attach-action')
                     ->setProp('modal-size', 'w-5/6');
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('lookup-url', sv_url($this->getLookupUrl()));
        $payload->set('attach-url', sv_url($this->getAttachUrl()));
        $payload->set('pivot-fields', $this->getPivotForm());
    }

    public function getLookupUrl()
    {
        return $this->relation->route('lookup', $this->relation->getParentEntry());
    }

    public function getAttachUrl()
    {
        return $this->relation->route('attach', $this->relation->getParentEntry());
    }

    public function setRelation(Relation $relation): LookupAttachablesAction
    {
        $this->relation = $relation;

        return $this;
    }

    protected function getPivotForm()
    {
        if ($pivotColumns = $this->relation->getRelationConfig()->getPivotColumns()) {
            return $this->relation->getPivotFields()->map(function (FieldInterface $field) {
                return (new FieldComposer($field))->forForm();
            });
        }
    }
}