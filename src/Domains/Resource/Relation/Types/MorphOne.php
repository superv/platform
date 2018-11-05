<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation implements ProvidesForm
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
    {
        $morphName = $this->config->getMorphName();

        return new EloquentMorphOne(
            $instance->newQuery(),
            $this->getParentEntry(),
            $morphName.'_type',
            $morphName.'_id',
            $this->getParentEntry()->getKeyName()
        );
    }

    protected function getRelatedEntry(): ?ResourceEntryModel
    {
        return $this->newQuery()->getResults();
    }

    public function makeForm(): Form
    {
        $relatedEntry = $this->getRelatedEntry() ?? $this->newRelatedInstance();

        $form = Form::of($relatedEntry->wrap());

        $form->removeFieldBeforeBuild(function (Field $field) {

            return starts_with($field->getColumnName(), $this->config->getMorphName().'_');
        });

        return $form;
    }
}