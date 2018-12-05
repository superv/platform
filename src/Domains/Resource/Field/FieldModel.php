<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class FieldModel extends ResourceEntry
{
    protected $table = 'sv_fields';

    protected $casts = [
        'rules'      => 'array',
        'config'     => 'array',
        'flags'     => 'array',
        'required'   => 'bool',
        'unique'     => 'bool',
        'searchable' => 'bool',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = uuid();
        });
    }

    public function getConfig()
    {
        return $this->config ?? [];
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

    public function isUnique(): bool
    {
        return (bool)$this->unique;
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

    public static function withUuid($uuid): self
    {
        return static::query()->where('uuid', $uuid)->firstOrFail();
    }
}