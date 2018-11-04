<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Entry\EntryModelV2;
use SuperV\Platform\Domains\Resource\ResourceModel;

class FieldModel extends EntryModelV2
{
    protected $table = 'sv_resource_fields';

    protected $casts = [
        'rules'      => 'array',
        'config'     => 'array',
        'required'   => 'bool',
        'unique'     => 'bool',
        'searchable' => 'bool',
    ];

    public function uuid()
    {
        return $this->uuid;
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

    public function getType()
    {
        return $this->type;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = Str::orderedUuid()->toString();
        });
    }
}