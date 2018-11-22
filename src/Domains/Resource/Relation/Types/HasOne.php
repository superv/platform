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
use SuperV\Platform\Domains\Resource\Resource;

class HasOne extends Relation implements ProvidesForm, HandlesRequests
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentHasOne(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getForeignKey(),
            $this->config->getLocalKey() ?? 'id'
        );
    }

    protected function getRelatedEntry(): ?EntryContract
    {
        if ($entry = $this->newQuery()->getResults()) {
            return $entry;
        }

        return null;
    }

    public function makeForm(): Form
    {
        $relatedEntry = $this->getRelatedEntry() ?? $this->newQuery()->make();

        return FormConfig::make()
                         ->setUrl(sprintf(
                             'sv/res/%s/%s/rel/%s',
                             $this->getParentResourceHandle(),
                             $this->parentEntry->getId(),
                             $this->getName()
                         ))
                         ->addGroup(Resource::of($relatedEntry), $relatedEntry)
                         ->hideField($parentBelongsToFieldName = Resource::of($this->parentEntry)->getResourceKey())
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