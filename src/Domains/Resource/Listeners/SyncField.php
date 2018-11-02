<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\ColumnDefinition;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SyncField
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldType */
    protected $fieldType;

    public function handle($event)
    {
        /** @var \SuperV\Platform\Domains\Database\ColumnDefinition $column */
        $column = $event->column;

        if ($column->autoIncrement || $column->type === 'timestamp') {
            return;
        }

        $this->setResourceEntry($event);

        if ($column->relation) {
            $relation = $column->getRelation();
            $column->ignore();

            if ($relation->type()->isBelongsTo()) {
                $column->type = 'integer';
                $relation->relationName(str_replace_last('_id', '', $column->name));
                $relation->foreignKey($column->name);
                $column->ignore(false);
            } elseif ($relation->hasPivotTable()) {
                $this->createPivotTable($relation);
            }

            $this->resourceEntry->resourceRelations()->create([
                'name'   => $relation->getName(),
                'type'   => $relation->getType(),
                'config' => $relation->toArray(),
            ]);

            return;
        }

        $this->mapFieldType($column);

        $field = $this->getFieldEntry($column->getFieldName());

        $this->sync($field, $column);
    }

    protected function sync(FieldModel $field, ColumnDefinition $column)
    {
        $field->type = $column->fieldType;
        $field->column_type = $column->type;
        $field->required = $column->isRequired();
        $field->unique = $column->isUnique();
        $field->searchable = $column->isSearchable();

        $field->config = $column->config;
        $field->rules = Rules::make($column->getRules())->get();

        $field->setDefaultValue($column->getDefaultValue());

        $field->save();

        if ($column->isTitleColumn()) {
            $field->getResourceEntry()->update(['title_field_id' => $field->getKey()]);
        }
    }

    /**
     * @param $column
     */
    protected function mapFieldType(ColumnDefinition $column): void
    {
        if (! $column->fieldType) {
            $mapper = ColumnFieldMapper::for($column->type)->map($column->toArray());

            $column->fieldType($mapper->getFieldType());

            $column->config(array_merge($column->getConfig(), $mapper->getConfig()));

            $column->rules(
                Rules::make($mapper->getRules())
                     ->merge($column->getRules())
                     ->get()
            );
        }

        $this->fieldType = FieldType::resolve($column->fieldType);
        $column->ignore(! $this->fieldType->hasColumn());

    }


    protected function createPivotTable($relation): void
    {
        if ($pivotColumnsCallback = $relation->getPivotColumns()) {
            $pivotColumnsCallback($table = new Blueprint(''));
            $relation->pivotColumns($table->getColumnNames());
        }

        if (! \Schema::hasTable($relation->getPivotTable())) {
            Schema::create(
                $relation->getPivotTable(),
                function (Blueprint $table) use ($relation, $pivotColumnsCallback) {
                    $table->increments('id');

                    if ($relation->type()->isMorphToMany()) {
                        $table->morphs($relation->getMorphName());
                    } else {
                        $table->unsignedBigInteger($relation->getPivotForeignKey());
                    }

                    $table->unsignedBigInteger($relation->getPivotRelatedKey());

                    if ($pivotColumnsCallback) {
                        $pivotColumnsCallback($table);
                    }

                    $table->timestamps();
                    $table->index([$relation->getPivotForeignKey()], md5(uniqid()));
                    $table->index([$relation->getPivotRelatedKey()], md5(uniqid()));
                });
        }
    }


    protected function getFieldEntry($fieldName)
    {
        if ($this->resourceEntry->hasField($fieldName)) {
          return $this->resourceEntry->getField($fieldName);
        }

        return $this->resourceEntry->createField($fieldName);
    }


    protected function setResourceEntry($event): void
    {
        if (isset($event->model)) {
            $resourceEntry = ResourceModel::withModel($event->model);
        }
        if (! isset($resourceEntry)) {
            $resourceEntry = ResourceModel::withSlug($event->table);
        }

        if (! $resourceEntry) {
            throw new \Exception("Resource model entry not found for table [{$event->table}]");
        }

        $this->resourceEntry = $resourceEntry;
    }
}