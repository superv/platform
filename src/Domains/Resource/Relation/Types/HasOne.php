<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasOne extends Relation implements ProvidesForm, HandlesRequests
{
    public function makeForm($request = null): \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
    {
        $formIdentifier = $this->getRelatedResource()->getIdentifier().'.forms:default';
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if ($request) {
            $builder->setRequest($request);
        }

        $builder->setEntry($this->getRelatedEntry());

        /** @var \SuperV\Platform\Domains\Resource\Form\Form $form */
        $form = $builder->getForm();

        $form->fields()->hide(sv_resource($this->parentEntry)->config()->getResourceKey());

        return $form;
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        return $this->makeForm($request)->save();
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        return new EloquentHasOne(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getForeignKey(),
            $this->relationConfig->getLocalKey() ?? 'id'
        );
    }
}
