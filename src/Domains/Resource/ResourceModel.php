<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\EntryModel;
use SuperV\Platform\Domains\Resource\Nav\NavModel;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

class ResourceModel extends EntryModel
{
    protected $table = 'sv_resources';

    protected $guarded = [];

    protected $casts = ['config' => 'array'];

    public function uuid()
    {
        return $this->uuid;
    }

    public function nav()
    {
        return $this->hasOne(NavModel::class, 'resource_id');
    }

    public function getFields(): Collection
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

    public function getResourceRelations()
    {
        return $this->resourceRelations;
    }

    public function getModelClass()
    {
        return array_get($this->config, 'model');
    }

    public function dropletEntry()
    {
        return $this->belongsTo(DropletModel::class, 'droplet_id');
    }

    public function getDropletEntry(): DropletModel
    {
        return $this->dropletEntry;
    }

    public function getDroplet()
    {
        return $this->droplet;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public static function withModel($model): ?self
    {
        return static::query()->where('model', $model)->first();
    }

    public static function withSlug($table): ?self
    {
        return static::fromCache($table);

        return static::query()->where('slug', $table)->first();
    }

    public static function fromCache($handle)
    {
        $cacheKey = 'sv:resources:'.$handle;

        $entry = cache()->rememberForever($cacheKey, function () use ($handle) {
            return static::query()->where('slug', $handle)->first();
        });

        return $entry;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->attributes['uuid'] = Str::orderedUuid()->toString();
        });

        static::deleting(function (ResourceModel $entry) {
            $entry->fields->map->delete();
        });
    }
}