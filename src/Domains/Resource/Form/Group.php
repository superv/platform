<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Group
{
    /** @var string */
    protected $handle;

    /**
     * @var Watcher
     */
    protected $watcher;

    /** @var \Illuminate\Support\Collection */
    protected $fields;

    /** @var \Illuminate\Support\Collection */
    protected $types;

    protected $skipFields = [];

    public function __construct(string $handle, Watcher $watcher = null, $fields)
    {
        $this->handle = $handle;
        $this->watcher = $watcher;
        $this->fields = is_array($fields) ? collect($fields) : $fields;
    }

    public function getWatcher(): ?Watcher
    {
        return $this->watcher;
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function build()
    {
        if ($this->watcher instanceof Entry) {
            $this->watcher = new ResourceEntry($this->watcher);
        }
        $this->types = collect();

        $this->fields = $this->fields->filter(function (Field $field) {
            return ! in_array($field->getName(), $this->skipFields);
        })->values();

        $this->fields = $this->fields
            ->map(function ($field) {
//                if (! $field instanceof Field) {
//                    throw new Exception('aaaaa');
//                    $field = FieldFactory::createFromEntry($field);
//                }

                if ($this->watcher) {
                    $field->setWatcher($this->watcher);
                    $field->setValueFromWatcher();
                }

                $this->types->push($type = FieldType::fromField($field));

                return $field;
            });
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function setSkipFields(array $skipFields): Group
    {
        $this->skipFields = $skipFields;

        return $this;
    }
}