<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTable;
use SuperV\Platform\Domains\Resource\Relation\Contracts\ProvidesField;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SyncField
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field */
    protected $field;

    protected $fieldWithoutEloquent = true;

    /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition */
    protected $column;

    /** @var \Illuminate\Support\Collection|\SuperV\Platform\Domains\Database\Schema\ColumnDefinition[] */
    protected $allColumns;

    /** @var \SuperV\Platform\Domains\Database\Schema\Blueprint */
    protected $blueprint;

    public function handle($event)
    {
        /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition $column */
        $this->column = $event->column;

        $this->blueprint = $event->blueprint;

        if ($this->column->autoIncrement) {
            return;
        }

        $this->setResourceEntry($event);

        if (! $this->handleRelations()) {
            return;
        }

        $this->mapFieldType();

        $this->checkMustBeCreated();

        $field = $this->syncWithEloquent();

        $formEntry = FormModel::findByResource($this->resourceEntry->getId());

        if ($formEntry) {
            $formEntry->fields()->attach($field->getId());
        }
    }

    protected function handleRelations()
    {
        if (! $this->column->relation) {
            return true;
        }

        $relationConfig = $this->column->getRelationConfig();
        $this->column->ignore();

        $relationType = Relation::resolve($relationConfig->getType());

        if ($relationConfig->hasPivotTable()) {
            (new CreatePivotTable)($relationConfig);
        }

        $this->resourceEntry->resourceRelations()->create([
            'name'   => $relationConfig->getName(),
            'type'   => $relationConfig->getType(),
            'config' => $relationConfig->toArray(),
        ]);

        if ($relationType instanceof ProvidesField) { //$relationConfig->type()->isBelongsTo()
            $this->column->ignore(false);
            $this->column->config($relationConfig->toArray());

            return true;
        } elseif ($relationConfig->type()->isMorphTo()) {
            $name = $relationConfig->getName();

            $this->blueprint->addPostBuildCallback(
                function (Blueprint $blueprint) use ($name) {
                    $blueprint->string("{$name}_type")->nullable($this->column->nullable);
                    $blueprint->unsignedBigInteger("{$name}_id")->nullable($this->column->nullable);
                    $blueprint->index(["{$name}_type", "{$name}_id"]);
                });

            $morphToField = $this->resourceEntry->createField($name);
            $morphToField->fill([
                'type'   => 'morph_to',
                'config' => RelationConfig::morphTo()
                                          ->relationName($name)
                                          ->toArray(),
                'flags'  => ['nullable'],
            ]);
            $morphToField->save();
        }

        return false;
    }

    protected function mapFieldType()
    {
        if (! $this->column->fieldType) {
            $mapper = ColumnFieldMapper::for($this->column->type)->map($this->column->toArray());

            $this->column->fieldType($mapper->getFieldType());

            $this->column->config(array_merge($this->column->getConfig(), $mapper->getConfig()));

            $this->column->rules(
                Rules::make($mapper->getRules())
                     ->merge($this->column->getRules())
                     ->get()
            );
        }

        $this->field = FieldType::resolveType($this->column->fieldType);
    }

    protected function syncWithEloquent(): FieldModel
    {
        $column = $this->column;

        $fieldName = $column->getFieldName();

        if ($this->resourceEntry->hasField($fieldName)) {
            $field = $this->resourceEntry->getField($fieldName);
        } else {
            $field = $this->resourceEntry->createField($fieldName);
        }

        $field->type = $column->fieldType;
        $field->column_type = $column->type;
//        $field->required = $column->isRequired();
//        $field->unique = $column->isUnique();
//        $field->searchable = $column->isSearchable();
        $config = $column->config;

        $field->flags = $column->flags;

//        if ($column->hide) {
//            $config['hide.'.$column->hide] = true;
//        }

        $config['default_value'] = $column->getDefaultValue();

        $field->config = array_filter_null($config);
        $field->rules = Rules::make($column->getRules())->get();
        $field->save();

        return $field;
    }

    protected function setResourceEntry($event): void
    {
        if (isset($event->model)) {
            $resourceEntry = ResourceModel::withModel($event->model);
        }
        if (! isset($resourceEntry)) {
            $resourceEntry = ResourceModel::withHandle($event->table);
        }

        if (! $resourceEntry) {
            throw new \Exception("Resource model entry not found for table [{$event->table}]");
        }

        $this->resourceEntry = $resourceEntry;
    }

    protected function checkMustBeCreated()
    {
        if (! $this->field instanceof RequiresDbColumn) {
            $this->column->ignore();
        }
    }
}