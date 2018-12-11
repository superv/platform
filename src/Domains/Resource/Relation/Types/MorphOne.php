<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation implements ProvidesForm, MakesEntry, HandlesRequests
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        $morphName = $this->relationConfig->getMorphName();

        return new EloquentMorphOne(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $morphName.'_type',
            $morphName.'_id',
            $this->parentEntry->getKeyName()
        );
    }

    public function makeForm(): Form
    {
        return Form::for($this->getRelatedEntry())
                   ->make();
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }

    /**
     * Create and return an un-saved instance of the related model.
     *
     * @param  array $attributes
     * @return \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    public function make(array $attributes = [])
    {
        return $this->newQuery()->make($attributes);
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        return $this->makeForm()->setRequest($request)->save();
    }
}