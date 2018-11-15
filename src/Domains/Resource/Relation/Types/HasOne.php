<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry as ConcreteResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasOne extends Relation implements ProvidesForm
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        return new EloquentHasOne(
            $relatedEntryInstance->newQuery(),
            $this->parentResourceEntry->getEntry(),
            $this->config->getForeignKey(),
            $this->config->getLocalKey() ?? 'id'
        );
    }

    protected function getRelatedEntry(): ?ResourceEntry
    {
        if ($entry = $this->newQuery()->getResults()) {
            return ConcreteResourceEntry::make($entry);
        }

        return null;
    }

//    protected function newRelatedInstance(): ?ResourceEntry
//    {
//        if ($model = $this->config->getRelatedModel()) {
//            return new ConcreteResourceEntry(new $model);
//        } elseif ($table = $this->config->getRelatedResource()) {
//            return ConcreteResourceEntry::newInstance($table);
//        }
//
//        throw new PlatformException('Related resource/model not found');
//    }

    public function makeForm(): Form
    {
        $relatedEntry = $this->getRelatedEntry() ?? ConcreteResourceEntry::make($this->newQuery()->make());


        $form = (new FormBuilder)
            ->addGroup($relatedEntry->getHandle(), $relatedEntry, $relatedEntry->getResource())
            ->removeField($this->parentResourceEntry->getResource()->getResourceKey())
            ->prebuild()
            ->getForm();

        return $form;
    }

    public function getFormTitle(): string
    {
        return $this->getName();
    }
}