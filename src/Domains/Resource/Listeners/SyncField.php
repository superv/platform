<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SyncField
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType */
    protected $fieldType;

    protected $fieldWithoutEloquent = true;

    public function handle($event)
    {
        /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition $column */
        $column = $event->column;

        if ($column->autoIncrement || $column->type === 'timestamp') {
            return;
        }

        $this->setResourceEntry($event);

        if ($column->relation) {
            $relation = $column->getRelation();
            $column->ignore();

            if ($relation->hasPivotTable()) {
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

        if ($this->fieldWithoutEloquent === true) {
            $this->fieldWithoutEloquent($column);
        } else {
            $field = $this->getFieldEntry($column->getFieldName());
            $this->sync($field, $column);
        }
    }

    protected function sync(FieldModel $field, ColumnDefinition $column)
    {
        $field->type = $column->fieldType;
        $field->column_type = $column->type;
        $field->required = $column->isRequired();
        $field->unique = $column->isUnique();
        $field->searchable = $column->isSearchable();
        $config = $column->config ?? [];

        if ($column->hide) {
            $config['hide.'.$column->hide] = true;
        }

        $config['default_value'] = $column->getDefaultValue();

        $field->config = array_filter_null($config);
        $field->rules = Rules::make($column->getRules())->get();
        $field->save();
    }

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
        $this->checkMustBeCreated($column);
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

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\ColumnDefinition $column
     */
    protected function checkMustBeCreated(ColumnDefinition $column): void
    {
        if (! $this->fieldType instanceof NeedsDatabaseColumn) {
            $column->ignore();
        }
    }

    protected function fieldWithoutEloquent(ColumnDefinition $column)
    {
        $resourceId = $this->resourceEntry->id;
        $fieldName = $column->getFieldName();

        $fieldObj = \DB::table('sv_fields')->where('resource_id', '=', $resourceId)->where('name', '=', $fieldName)->first();

        if (! $fieldObj) {
            $field = [
                'name'        => $fieldName,
                'resource_id' => $resourceId,
                'uuid'        => uuid(),
                'config'      => $column->config ?? [],
                'rules'       => Rules::make($column->getRules())->get(),
            ];
        } else {
//            $field = [];
//            foreach(get_object_vars($fieldObj) as $key => $value) {
//                $field[$key] = $value;
//            }
            $field = (array)$fieldObj;
            $field['config'] = array_merge(json_decode($field['config'] ?? "[]", true), $column->config);
            $field['rules'] = array_merge(json_decode($field['rules'] ?? "[]", true), Rules::make($column->getRules())->get());
        }

        $field['type'] = $column->fieldType;
        $field['column_type'] = $column->type;
        $field['required'] = $column->isRequired();
        $field['unique'] = $column->isUnique();
        $field['searchable'] = $column->isSearchable();

        if ($column->hide) {
            $field['config']['hide.'.$column->hide] = true;
        }

        $field['config']['default_value'] = $column->getDefaultValue();

        $field['rules'] = json_encode(array_filter_null($field['rules']));
        $field['config'] = json_encode(array_filter_null($field['config']));


        if (isset($field['id'])) {
            \DB::table('sv_fields')->where('id', $field['id'])->update($field);
        } else {
            \DB::table('sv_fields')->insert($field);
        }
    }
}