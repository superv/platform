<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Resource;

class Fields
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

    public function __invoke()
    {
        return $this->fields;
    }

    public function get($name): Field
    {
        return $this->fields->first(
            function (Field $field) use ($name) {
                return $field->getName() === $name;
            });
    }

    public function showOnIndex($name): Field
    {
        return $this->get($name)->showOnIndex();
    }

    public function keyByName(): Collection
    {
        return $this->fields->keyBy(function (Field $field) {
            return $field->getName();
        });
    }

    public function withFlag($flag): Collection
    {
        return $this->fields->filter(function (Field $field) use ($flag) {
            return $field->hasFlag($flag);
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

    public function getFilters(): Collection
    {
        $filters = $this->fields->filter(function (Field $field) {
            return $field->hasFlag('filter');
        })->map(function (Field $field) {
            $fieldType = $field->resolveFieldType();
            if ($fieldType instanceof ProvidesFilter) {
                return $fieldType->makeFilter();
            }
        });

        return $filters->filter();
    }
}