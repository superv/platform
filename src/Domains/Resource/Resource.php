<?php

namespace SuperV\Platform\Domains\Resource;

use Exception;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Builder;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource
{
    use Hydratable;

    protected $uuid;

    /** @var \Illuminate\Support\Collection */
    protected $fields;

    /** @var \SuperV\Platform\Domains\Resource\ResourceEntryModel */
    protected $entry;

    protected $entryId;

    protected $model;

    protected $slug;

    /** @var boolean */
    protected $built = false;

    public function build()
    {
        if (! $this->entry) {
            $this->entry = $this->resolveModel();
        }

        $this->fields = $this->fields
            ->map(function (FieldModel $field) {
                return (new Builder($this))->build($field);
            });

        $this->built = true;

        return $this;
    }

    public function resolveModel()
    {
        if ($this->model) {
            return app($this->model);
        }

        $model = new class extends ResourceEntryModel
        {
            public $timestamps = false;

            public static $entryTable;

            public function getMorphClass()
            {
                return static::$entryTable;
            }

            public function setTable($table)
            {
                $this->table = static::$entryTable = $table;
            }

            public static function __callStatic($method, $parameters)
            {
                $static = (new static);
                $static->setTable($static::$entryTable);

                return $static->$method(...$parameters);
            }
        };
        $model->setTable($this->getSlug());

        return $model;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function loadEntry($entryId)
    {
        return $this->entry = $this->resolveModel()->newQuery()->find($entryId);
    }

    public function saveEntry()
    {
        $this->getEntry()->save();
    }

    public function getEntry(): ?ResourceEntryModel
    {
        return $this->entry;
    }

    public function getEntryId()
    {
        return $this->entry ? $this->entry->id : null;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function setFields(Collection $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFieldEntry($name): ?FieldModel
    {
        return optional($this->getField($name))->getEntry();
    }

    public function getField($name): ?FieldType
    {
        $this->checkState();

        return $this->fields->first(function (FieldType $field) use ($name) { return $field->getName() === $name; });
    }

    public function checkState()
    {
        if (! $this->isBuilt()) {
            throw new Exception('Resource is not built yet');
        }
    }

    /**
     * @return bool
     */
    public function isBuilt()
    {
        return $this->built;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function route($route)
    {
        $base = 'sv/resources/'.$this->getSlug();
        if ($route === 'edit') {
            return $base . '/'.$this->getEntryId(). '/edit';
        }
        if ($route === 'create') {
            return $base . '/create';
        }

    }

    public function __sleep()
    {
        if ($this->entry && $this->entry->exists) {
            $this->entryId = $this->entry->getKey();
        }

        return array_diff(array_keys(get_object_vars($this)), ['entry']);
    }

    public function __wakeup()
    {
        if ($this->entryId) {
            $this->loadEntry($this->entryId);
        } else {
            $this->entry = $this->resolveModel();
        }
    }
}