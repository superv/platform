<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\Jobs\BuildFormDeprecated;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation implements ProvidesForm, MakesEntry
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        $morphName = $this->config->getMorphName();

        return new EloquentMorphOne(
            $relatedEntryInstance->newQuery(),
            $this->getParentEntry(),
            $morphName.'_type',
            $morphName.'_id',
            $this->getParentEntry()->getKeyName()
        );
    }

    protected function getRelatedEntry(): ?ResourceEntry
    {
        if ($entry = $this->newQuery()->getResults()) {
            return ResourceEntry::make($entry);
        }
    }

    public function makeForm(): Form
    {
        $relatedEntry = $this->getRelatedEntry() ?? $this->newRelatedInstance();

        $form = (new FormBuilder)
            ->addGroup($relatedEntry->getHandle(), $relatedEntry, $relatedEntry->getResource())
            ->prebuild()
            ->getForm();


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