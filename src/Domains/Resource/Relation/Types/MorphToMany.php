<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Action\AttachEntryAction;
use SuperV\Platform\Domains\Resource\Action\DetachEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\TableConfig;

class MorphToMany extends Relation implements ProvidesTable, ProvidesQuery
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentMorphToMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getMorphName(),
            $this->config->getPivotTable(),
            $this->config->getPivotForeignKey(),
            $this->config->getPivotRelatedKey(),
            $this->parentEntry->getKeyName(),
            $relatedEntryInstance->getKeyName()
        );
    }

    public function makeTableConfig(): TableConfig
    {
        $resource = Resource::of($this->getConfig()->getRelatedResource());

        $fields = $resource->getFields();

        if ($pivotColumns = $this->getConfig()->getPivotColumns()) {
            $pivotResource = Resource::of($this->getConfig()->getPivotTable());
            $pivotFields = $pivotResource->getFields()
                                         ->filter(function (Field $field) use ($pivotColumns) {
                                             return in_array($field->getColumnName(), $pivotColumns);
                                         })
                                         ->map(function (Field $field) {
                                             $field->onPresenting(function ($value) use ($field) {
                                                 if (is_object($value) && $pivot = $value->pivot) {
                                                     return $pivot->{$field->getColumnName()};
                                                 }
                                             });

                                             return $field;
                                         })
                                         ->values()->all();

            $fields = $fields->merge($pivotFields);
        }

        $config = new TableConfig;
        $config->setColumns($fields);
        $config->setQuery($this->newQuery());

        $config->setRowActions([DetachEntryAction::make()->setRelation($this)]);
        $config->setContextActions([AttachEntryAction::make()->setRelation($this)]);

        $config->setTitle($this->getName());
        $config->build(false);
        $config->setDataUrl(url()->current().'/data');

        return $config;
    }
}