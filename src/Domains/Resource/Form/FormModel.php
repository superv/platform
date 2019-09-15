<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class FormModel extends Entry
{
    protected $table = 'sv_forms';

    protected $ownerResource;

    protected $casts = ['public' => 'boolean'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (FormModel $entry) {
            if (is_null($entry->uuid)) {
//                $entry->setAttribute('uuid', uuid());
            }
            $entry->setAttribute('rev_id', uuid());
        });
    }

    public function created_by()
    {
        return $this->belongsTo(config('superv.auth.user.model'), 'created_by_id');
    }

    public function updated_by()
    {
        return $this->belongsTo(config('superv.auth.user.model'), 'updated_by_id');
    }

    public function fields()
    {
        return $this->belongsToMany(FieldModel::class, 'sv_form_fields', 'form_id', 'field_id');
    }

    public function resource()
    {
        return $this->belongsTo(ResourceModel::class, 'resource_id');
    }

    public function attachField($fieldEntryId)
    {
        $this->fields()->attach($fieldEntryId);
    }

    public function isPublic()
    {
        return $this->public;
    }

    public function isPrivate()
    {
        return ! $this->public;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return sprintf("sv/forms/%s", $this->uuid);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getOwnerResource()
    {
        if ($this->resource_id > 0 && ! $this->ownerResource) {
            $this->load('resource');
            /** @var ResourceModel $resourceEntry */
            $resourceEntry = $this->getRelation('resource');

            $this->ownerResource = ResourceFactory::make($resourceEntry->getIdentifier());
        }

        return $this->ownerResource;
    }

    public function getFormFields(): Collection
    {
        return $this->fields()->get();
    }

    public function getFormField($name)
    {
        return $this->fields()->where('name', $name)->first();
    }

    public function compileFields(): Collection
    {
        return $this->getFormFields()
                    ->map(function (FieldModel $fieldEntry) {
                        $field = FieldFactory::createFromEntry($fieldEntry);

                        if ($this->resource_id > 0) {
                            $field->setResource($this->getOwnerResource());
                        }

                        return $field;
                    });
    }

    public static function withIdentifier($identifier): ?FormModel
    {
        return static::query()->where('identifier', $identifier)->first();
    }
    public static function findByUuid($uuid): ?FormModel
    {
        return static::query()->where('uuid', $uuid)->first();
    }

    public static function findByResource($id): ?FormModel
    {
        return static::query()->where('resource_id', $id)->first();
    }
}
