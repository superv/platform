<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Resource\Field\FieldModel;

class ResourceModel extends Model
{
    protected $table = 'sv_resources';

    protected $guarded = [];

    public static function withModel($model): ?self
    {
        return static::query()->where('model', $model)->first();
    }

    public static function withSlug($table): ?self
    {
        return static::query()->where('slug', $table)->first();
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

    public function uuid()
    {
        return $this->uuid;
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getField($name): ?FieldModel
    {
        return $this->fields()->where('name', $name)->first();
    }

    public function fields()
    {
        return $this->hasMany(FieldModel::class, 'resource_id');
    }

    public function createField($name): FieldModel
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

    public function getModelClass()
    {
        return $this->model;
    }

    public function dropletEntry()
    {
        return $this->belongsTo(DropletModel::class, 'droplet_id');
    }

    public function getDropletEntry(): DropletModel
    {
        return $this->dropletEntry;
    }

    public function getDropletSlug()
    {
        return $this->droplet_slug;
    }

    public function getTitleFieldId()
    {
        return $this->title_field_id;
    }

    public function getSlug()
    {
        return $this->slug;
    }
}