<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTable;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SyncField
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType */
    protected $fieldType;

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

        if ($this->column->autoIncrement || $this->column->type === 'timestamp') {
            return;
        }

        $this->setResourceEntry($event);

        if ($this->column->relation) {
            $this->handleRelationConfig();

            return;
        }

        $this->mapFieldType();
        $this->checkMustBeCreated();

//        $field = $this->syncWithPDO();
        $field = $this->syncWithEloquent();
//        $field = $this->syncWithResource();

//        if (! Current::envIsTesting()) {
//            if (! $this->column->ignore) {
//                if (ResourceModel::withSlug('sv_meta_items')) {
//                    (new Repository)
//                        ->save(
//                            Meta::make($field['config'] ?? [])
//                                ->setOwner('sv_fields', $field['id'], 'config')
//                        );
//
//                    (new Repository)
//                        ->save(
//                            Meta::make($field['rules'] ?? [])
//                                ->setOwner('sv_fields', $field['id'], 'rules')
//                        );
//                }
//            }
//        }
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

        $this->fieldType = FieldTypeV2::resolve($this->column->fieldType);
    }

    protected function syncWithPDO()
    {
        $column = $this->column;
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

        $dataArray = array_merge($field, [
            'rules'  => json_encode(array_filter_null($field['rules'])),
            'config' => json_encode(array_filter_null($field['config'])),
        ]);
        if (isset($field['id'])) {
            \DB::table('sv_fields')->where('id', $field['id'])->update($dataArray);
        } else {
            $field['id'] = \DB::table('sv_fields')->insertGetId($dataArray);
        }

        return $field;
    }

    protected function syncWithEloquent()
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

        return $field->toArray();
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

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\ColumnDefinition $column
     */
    protected function checkMustBeCreated()
    {
        if (! $this->fieldType instanceof NeedsDatabaseColumn) {
            $this->column->ignore();
        }
    }

    protected function handleRelationConfig()
    {
        $relationConfig = $this->column->getRelationConfig();
        $this->column->ignore();

        if ($relationConfig->hasPivotTable()) {
            CreatePivotTable::dispatch($relationConfig);
        }

        if ($relationConfig->type()->isMorphTo()) {
            $name = $relationConfig->getName();

            $this->blueprint->addPostBuildCallback(
                function (Blueprint $blueprint) use ($name) {
                    $blueprint->string("{$name}_type");
                    $blueprint->unsignedBigInteger("{$name}_id");
                    $blueprint->index(["{$name}_type", "{$name}_id"]);
                });
        }

        $this->resourceEntry->resourceRelations()->create([
            'name'   => $relationConfig->getName(),
            'type'   => $relationConfig->getType(),
            'config' => $relationConfig->toArray(),
        ]);
    }
}