<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormModel extends ResourceEntry
{
    protected $table = 'sv_forms';

    protected $ownerResource;

    protected $casts = ['public' => 'boolean'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (ResourceEntry $entry) {
            if (is_null($entry->uuid)) {
                $entry->setAttribute('uuid', uuid());
            }
        });
    }

    public function isPublic()
    {
        return $this->public;
    }

    public function getUrl()
    {
        return sprintf("sv/forms/%s", $this->uuid);
    }

    public function getOwnerResource()
    {
        if ($this->resource_id > 0 && !$this->ownerResource) {
            $this->load('resource');
            $resourceEntry = $this->getRelation('resource');

            $this->ownerResource = ResourceFactory::make($resourceEntry->getHandle());
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
                    ->map(function (FieldModel $fielEntry)  {
                        $field = FieldFactory::createFromEntry($fielEntry);

                        if ($this->resource_id > 0) {
                            $field->setResource($this->getOwnerResource());
                        }

                        return $field;
                    });
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