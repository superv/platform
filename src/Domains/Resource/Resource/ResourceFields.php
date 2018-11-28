<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceFields
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    public function __construct(Resource $resource, $fields)
    {
        $this->resource = $resource;
        $this->fields = $fields instanceof Closure ? $fields() : $fields;
    }

    public function get($name = null)
    {
        if (is_null($name)) {
            return $this->fields;
        }

        return $this->fields->first(
            function (Field $field) use ($name) {
                return $field->getName() === $name;
            });
    }

    public function keyByName(): Collection
    {
        return $this->fields->keyBy(function (Field $field) {
            return $field->getName();
        });
    }

    public function forTable(): Collection
    {
        $label = FieldFactory::createFromArray([
            'type'  => 'text',
            'name'  => 'label',
            'label' => $this->resource->getSingularLabel(),
        ]);
        $label->setCallback('table.presenting', function (EntryContract $entry) {
            return sv_parse($this->resource->getConfigValue('entry_label'), $entry->toArray());
        })->showOnIndex();

        return collect()->put('label', $label)
                        ->merge($this->fields
                            ->filter(function (Field $field) {
                                return $field->hasFlag('table.show');
                            })
                        );
    }
}