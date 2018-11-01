<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\ColumnDefinition;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\ColumnFieldMapper;
use SuperV\Platform\Domains\Resource\ResourceModel;

class FieldModel extends Model
{
    protected $table = 'sv_resource_fields';

    protected $guarded = [];

    protected $casts = [
        'rules'      => 'array',
        'config'     => 'array',
        'required'   => 'bool',
        'unique'     => 'bool',
        'searchable' => 'bool',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = Str::orderedUuid()->toString();
        });
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function sync(ColumnDefinition $column)
    {
        $this->column_type = $column->type;
        $this->mapColumn($column);

        $this->required = $column->isRequired();
        $this->unique = $column->isUnique();
        $this->searchable = $column->isSearchable();

        $this->setDefaultValue($column->getDefaultValue());

        $this->save();

        if ($column->isTitleColumn()) {
            $this->getResourceEntry()->update(['title_field_id' => $this->getKey()]);
        }
    }

    /**
     * @param \SuperV\Platform\Domains\Database\ColumnDefinition $column
     */
    protected function mapColumn(ColumnDefinition $column): void
    {
        if ($fieldType = $column->getFieldType()) {
            $this->field_type = $fieldType;
            $this->rules = $column->rules;

            if ($fieldType === 'relation') {
                $this->config = $this->makeRelationConfig($column);
            } else {
                $this->config = $column->config;
            }
        } else {
            $mapper = ColumnFieldMapper::for($column->type)->map($column->parameters);

            $this->field_type = $mapper->getFieldType();
            $this->rules = array_merge($column->getRules(), $mapper->getRules());
            $this->config = array_merge($column->getConfig(), $mapper->getConfig());
        }
    }

    protected function makeRelationConfig(ColumnDefinition $column)
    {
        $relation = $column->getRelation();

        if ($relation['type'] === 'belongs_to') {
            $column->type = 'integer';
            $this->name = str_replace_last('_id', '', $column->name);
            $relation = array_merge($relation, ['foreign_key' => $column->name]);
        } elseif (in_array($relation['type'], ['belongs_to_many', 'morph_to_many'])) {
            if ($pivotColumnsCallback = array_get($relation, 'pivot_columns')) {
                $pivotColumnsCallback($table = new Blueprint(''));
                $relation['pivot_columns'] = $table->getColumnNames();
            }
            $pivotTable = $relation['pivot_table'];
            if (! \Schema::hasTable($pivotTable)) {
                Schema::create(
                    $pivotTable,
                    function (Blueprint $table) use ($relation, $pivotColumnsCallback) {
                        $table->increments('id');

                        if ($relation['type'] === 'morph_to_many') {
                            $table->morphs($relation['morph_name']);
                        } else {
                            $table->unsignedBigInteger($relation['pivot_foreign_key']);
                        }

                        $table->unsignedBigInteger($relation['pivot_related_key']);

                        if ($pivotColumnsCallback) {
                            $pivotColumnsCallback($table);
                        }

                        $table->timestamps();
                        $table->index([$relation['pivot_foreign_key']], md5(uniqid()));
                        $table->index([$relation['pivot_related_key']], md5(uniqid()));
                    });
            }
        }

        if (in_array($relation['type'], ['belongs_to_many', 'morph_to_many', 'has_many'])) {
            $column->ignore();
        }

        return $relation;
    }

    public function setDefaultValue($value)
    {
        if ($value) {
            $this->setConfigValue('default_value', $value);
        }
    }

    public function setConfigValue($key, $value)
    {
        $config = $this->getConfig();
        $config[$key] = $value;

        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config ?? [];
    }

    public function getResourceEntry()
    {
        return $this->resource;
    }

    public function resource()
    {
        return $this->belongsTo(ResourceModel::class, 'resource_id');
    }

    public function getColumnType()
    {
        return $this->column_type;
    }

    public function getFieldType()
    {
        return $this->field_type;
    }

    public function isSearchable(): bool
    {
        return (bool)$this->searchable;
    }

    public function isNullable(): bool
    {
        return ! $this->isRequired();
    }

    public function isRequired(): bool
    {
        return (bool)$this->required;
    }

    public function getDefaultValue()
    {
        return $this->getConfigValue('default_value');
    }

    public function getConfigValue($key, $default = null)
    {
        return array_get($this->getConfig(), $key, $default);
    }

    public function getRules()
    {
        $rules = array_filter($this->rules ?? []);
        if ($this->isUnique()) {
            $rules[] = 'unique:'.$this->getResourceTable().','.$this->getName().',{entry.id},id';
        }

        return $rules;
    }

    public function isUnique(): bool
    {
        return (bool)$this->unique;
    }

    public function getResourceTable()
    {
        return $this->getResourceEntry()->getSlug();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRulesAttribute($rules)
    {
        if (! $rules) {
            return;
        }

        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->attributes['rules'] = json_encode($rules);
    }
}