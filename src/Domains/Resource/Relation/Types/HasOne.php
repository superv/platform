<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class HasOne extends Relation implements ProvidesForm, HandlesRequests
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentHasOne(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getForeignKey(),
            $this->relationConfig->getLocalKey() ?? 'id'
        );
    }

    public function makeForm(): Form
    {
        return FormConfig::make($this->getRelatedEntry())
                         ->hideField(ResourceFactory::make($this->parentEntry)->getResourceKey().'_id')
                         ->makeForm();
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        return $this->makeForm()->setRequest($request)->save();
    }
}