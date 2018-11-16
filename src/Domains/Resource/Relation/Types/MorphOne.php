<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry as ConcreteResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation implements ProvidesForm, MakesEntry
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        $morphName = $this->config->getMorphName();

        return new EloquentMorphOne(
            $relatedEntryInstance->newQuery(),
            $this->parentResourceEntry->getEntry(),
            $morphName.'_type',
            $morphName.'_id',
            $this->parentResourceEntry->getEntry()->getKeyName()
        );
    }

    protected function getRelatedEntry(): ?ResourceEntry
    {
        if ($entry = $this->newQuery()->getResults()) {
            return ConcreteResourceEntry::make($entry);
        }

        return null;
    }

    public function makeForm(): Form
    {
        $relatedEntry = $this->getRelatedEntry() ?? $this->newRelatedInstance();

        $form = FormConfig::make()
                          ->addGroup($relatedEntry->getResource(), $relatedEntry)
                          ->makeForm();

        return $form;
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }

    /**
     * Create and return an un-saved instance of the related model.
     *
     * @param  array $attributes
     * @return \SuperV\Platform\Domains\Database\Model\Entry
     */
    public function make(array $attributes = [])
    {
        return $this->newQuery()->make($attributes);
    }
}