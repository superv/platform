<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Current;
use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Jobs\CreatePivotTable;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Meta\Meta;
use SuperV\Platform\Support\Meta\Repository;

class SyncField
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType */
    protected $fieldType;

    protected $fieldWithoutEloquent = true;

    /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition */
    protected $column;

    public function handle($event)
    {
        /** @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition $column */
        $this->column = $event->column;

        if ($this->column->autoIncrement || $this->column->type === 'timestamp') {
            return;
        }

        $this->setResourceEntry($event);

        if ($this->column->relation) {
            return $this->handleRelationConfig();
        }

        $this->mapFieldType();
        $this->checkMustBeCreated();

        if ($this->fieldWithoutEloquent === true) {
            $this->fieldWithoutEloquent($this->column);
        } else {
            $field = $this->getFieldEntry($this->column->getFieldName());
            $this->sync($field, $this->column);
        }
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

        $this->fieldType = FieldType::resolve($this->column->fieldType);
    }

    protected function fieldWithoutEloquent()
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

        $theConfig = $field['config'];
        if ($column->hide) {
            $theConfig['hide.'.$column->hide] = true;
        }
        $theConfig['default_value'] = $column->getDefaultValue();

        $field['rules'] = json_encode(array_filter_null($field['rules']));
        $field['config'] = json_encode(array_filter_null($theConfig));

        if (isset($field['id'])) {
            \DB::table('sv_fields')->where('id', $field['id'])->update($field);
        } else {
            $field['id'] = \DB::table('sv_fields')->insertGetId($field);
        }

        if (! Current::envIsTesting()) {
            if (! $column->ignore) {
                if (ResourceModel::withSlug('sv_meta_items')) {
                    $meta = Meta::make($theConfig)->setOwner('sv_fields', $field['id'], 'config');
                    (new Repository)->save($meta);
                }
            }
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
    protected function checkMustBeCreated()
    {
        if (! $this->fieldType instanceof NeedsDatabaseColumn) {
            $this->column->ignore();
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

    protected function handleRelationConfig()
    {
        $relationConfig = $this->column->getRelationConfig();
        $this->column->ignore();

        if ($relationConfig->hasPivotTable()) {
            CreatePivotTable::dispatch($relationConfig);
        }

        $this->resourceEntry->resourceRelations()->create([
            'name'   => $relationConfig->getName(),
            'type'   => $relationConfig->getType(),
            'config' => $relationConfig->toArray(),
        ]);
    }
}