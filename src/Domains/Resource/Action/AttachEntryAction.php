<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Composer\Composition;

class AttachEntryAction extends Action
{
    protected $name = 'attach';

    protected $title = 'Attach New';

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    public function makeComponent()
    {
        return parent::makeComponent()
                     ->setName('sv-attach-entry-action');
    }

    protected function getPivotForm()
    {
        if ($pivotColumns = $this->relation->getConfig()->getPivotColumns()) {
            $pivotResource = Resource::of($this->relation->getConfig()->getPivotTable());

            $pivotFields = $pivotResource->getFields()
                                         ->filter(function(Field $field) use ($pivotColumns) {
                                             return in_array($field->getColumnName(), $pivotColumns);
                                         })
                                         ->values()->all();

            $form = FormConfig::make()
                              ->addGroup($pivotFields)
                              ->makeForm();

            return $form->compose()->get('fields');
        }
    }

    public function onComposed(Composition $composition)
    {
        $composition->replace('lookup-url', sv_url($this->getLookupUrl()));
        $composition->replace('attach-url', sv_url($this->getAttachUrl()));
        $composition->replace('pivot-fields', $this->getPivotForm());
    }

    public function getLookupUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/lookup',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentEntry()->getId(),
            $this->relation->getName()
        );
    }

    public function getAttachUrl()
    {
        return sprintf(
            'sv/res/%s/%s/%s/attach',
            $this->relation->getParentResourceHandle(),
            $this->relation->getParentEntry()->getId(),
            $this->relation->getName()
        );
    }

    public function setRelation(Relation $relation): AttachEntryAction
    {
        $this->relation = $relation;

        return $this;
    }
}