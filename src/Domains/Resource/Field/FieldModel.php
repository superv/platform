<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class FieldModel extends ResourceEntry
{
    protected $table = 'sv_fields';

    protected $casts = [
        'rules'      => 'array',
        'config'     => 'array',
        'required'   => 'bool',
        'unique'     => 'bool',
        'searchable' => 'bool',
    ];

//
//    public function setDefaultValue($value)
//    {
//        if ($value) {
//            $this->setConfigValue('default_value', $value);
//        }
//    }

//    public function setConfigValue($key, $value)
//    {
//        $config = $this->getConfig();
//        $config[$key] = $value;
//
//        $this->config = $config;
//    }

    public function getConfig()
    {
        return $this->config ?? [];
    }
//
//    public function getResourceEntry()
//    {
//        return $this->resourceEntry;
//    }
//
//    public function resourceEntry()
//    {
//        return $this->belongsTo(ResourceModel::class, 'resource_id');
//    }

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
        return $this->rules;
    }

    public function isUnique(): bool
    {
        return (bool)$this->unique;
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

    public function uuid()
    {
        return $this->uuid;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = Str::orderedUuid()->toString();
        });
    }

    public static function withUuid($uuid): self
    {
        return static::query()->where('uuid', $uuid)->firstOrFail();
    }
}