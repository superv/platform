<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\EntryForm;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation implements ProvidesForm, MakesEntry, HandlesRequests
{
    public function makeForm(): EntryForm
    {
        $form = ResourceFormBuilder::buildFromEntry($this->getRelatedEntry());
        $formData = FormModel::findByUuid($this->getRelatedResource()->getIdentifier());

        return $form->make($formData ? $formData->uuid : null)
                    ->hideField($this->relationConfig->getMorphName());
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }

    /**
     * Create and return an un-saved instance of the related model.
     *
     * @param array $attributes
     */
    public function make(array $attributes = [])
    {
        return $this->newQuery()->make($attributes);
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        return $this->makeForm()->setRequest($request)->save();
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
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
}
