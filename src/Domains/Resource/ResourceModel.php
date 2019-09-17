<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

class ResourceModel extends Entry implements ProvidesFields
{
    protected $table = 'sv_resources';

    protected $guarded = [];

    protected $casts = [
        'config' => 'array',
        'driver' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (ResourceModel $entry) {
            $entry->attributes['uuid'] = uuid();
        });

        static::deleting(function (ResourceModel $entry) {
            $entry->fields->map->delete();
            $entry->forms->map->delete();
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

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDsn()
    {
        return $this->dsn;
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

    public function getForeignKey()
    {
        return 'resource_id';
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

    public function forms()
    {
        return $this->hasMany(FormModel::class, 'resource_id');
    }

    public function makeField(string $name): FieldModel
    {
        if ($this->hasField($name)) {
            throw new \Exception("Field with name [{$name}] already exists");
        }

        return $this->fields()->make([
            'revision_id' => uuid(),
            'namespace'   => $this->getIdentifier().'.fields',
            'identifier'  => $this->getIdentifier().'.fields.'.$name,
            'name'        => $name,
        ]);
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

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function wipeCache()
    {
        $cacheKey = 'sv:resources:'.$this->getIdentifier();
        cache()->forget($cacheKey);
    }

    public function created_by()
    {
        return $this->belongsTo(config('superv.auth.user.model'), 'created_by_id');
    }

    public function updated_by()
    {
        return $this->belongsTo(config('superv.auth.user.model'), 'updated_by_id');
    }

    public static function withModel($model): ?self
    {
        return static::query()->where('model', $model)->first();
    }

    public static function withIdentifier($identifier): ?self
    {
        return static::fromCache($identifier);
    }

    public static function fromCache($identifier)
    {
        $cacheKey = 'sv:resources:'.$identifier;

        $entry = cache()->rememberForever($cacheKey, function () use ($identifier) {
            return static::query()->where('identifier', $identifier)->first();
        });

        return $entry;
    }
}
