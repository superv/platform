<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasOne extends Relation implements ProvidesForm
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
    {
        $parentModel = $this->resource->getEntry();

        return new EloquentHasOne(
            $instance->newQuery(),
            $parentModel,
            $this->config->getForeignKey(),
            $this->config->getLocalKey() ?? 'id'
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
            if ( !$field instanceof BelongsTo) {
                return false;
            }
            return $field->getName() === str_singular($this->resource->slug());
        });

        return $form;
    }
}