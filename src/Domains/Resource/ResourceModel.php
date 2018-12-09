<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

class ResourceModel extends Entry implements ProvidesFields
{
    protected $table = 'sv_resources';

    protected $guarded = [];

    protected $casts = ['config' => 'array'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (ResourceModel $entry) {
            $entry->attributes['uuid'] = uuid();
        });

        static::deleting(function (ResourceModel $entry) {
            $entry->fields->map->delete();
            $entry->wipeCache();
        });

        static::saving(function (ResourceModel $entry) {
            $entry->wipeCache();
        });
    }

    public function nav()
    {
        return $this->hasOne(Section::class, 'resource_id');
    }

    public function provideFields(): Collection
    {
        return $this->getFields();
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getConfig()
    {
        return $this->config ?? [];
    }

    public function getConfigValue($key, $default = null)
    {
        return array_get($this->getConfig(), $key, $default);
    }

    public function getField($name): ?FieldModel
    {
        return $this->fields()->where('name', $name)->first();
    }

    public function fields()
    {
        return $this->hasMany(FieldModel::class, 'resource_id');
    }

    public function createField(string $name): FieldModel
    {
        if ($this->hasField($name)) {
            throw new \Exception("Field with name [{$name}] already exists");
        }

        return $this->fields()->make(['name' => $name]);
    }

    public function hasField($name)
    {
        return $this->fields()->where('name', $name)->exists();
    }

    public function resourceRelations()
    {
        return $this->hasMany(RelationModel::class, 'resource_id');
    }

    public function getResourceRelations(): Collection
    {
        return $this->resourceRelations;
    }

    public function getModelClass()
    {
        return array_get($this->config, 'model');
    }

    public function getAddon()
    {
        return $this->addon;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function wipeCache()
    {
        $cacheKey = 'sv:resources:'.$this->getHandle();
        cache()->forget($cacheKey);
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public static function withModel($model): ?self
    {
        return static::query()->where('model', $model)->first();
    }

    public static function withHandle($table): ?self
    {
        return static::fromCache($table);
    }

    public static function fromCache($handle)
    {
        $cacheKey = 'sv:resources:'.$handle;

        $entry = cache()->rememberForever($cacheKey, function () use ($handle) {
            return static::query()->where('slug', $handle)->first();
        });

        return $entry;
    }
}