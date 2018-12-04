<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class IndexFields
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $hideLabelField = false;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;

        $this->fields = $resource->fields()->getAll();
    }

    public function getField($name): Field
    {
        $field = $this->fields->first(
            function (Field $field) use ($name) {
                return $field->getName() === $name;
            });

        if (! $field) {
            PlatformException::fail("Field not found: [{$name}]");
        }

        return $field;
    }

    public function hide($name)
    {
        $field = $this->getField($name);
        $field->removeFlag('table.show');

        return $field;
    }

    public function showFirst($name, $label = null)
    {
        return $this->show($name, $label)->displayOrder(-999);
    }

    public function showLast($name, $label = null)
    {
        return $this->show($name, $label)->displayOrder(+999);
    }

    public function show($name, $label = null)
    {
        $field = $this->getField($name);
        $field->showOnIndex();

        if ($label) {
            $field->setLabel($label);
        }

        return $field;
    }

    public function add($field)
    {
        if (is_array($field)) {
            $field = FieldFactory::createFromArray($field);
        }

        $field->showOnIndex();

        $this->fields->push($field);

        return $field;
    }

    public function hideLabel()
    {
        $this->hideLabelField = true;

        return $this;
    }

    protected function makeLabelField()
    {
        $fieldParams = [
            'type'  => 'text',
            'name'  => 'label',
            'label' => $this->resource->getSingularLabel(),
        ];

        return FieldFactory::createFromArray($fieldParams)
                           ->setCallback('table.presenting',
                               function (EntryContract $entry) {
                                   return sv_parse(
                                       $this->resource->getConfigValue('entry_label'),
                                       $entry->toArray()
                                   );
                               })
                           ->showOnIndex()
                           ->displayOrder(-1);
    }

    public function get()
    {
        if ($this->hideLabelField === false) {
            $this->fields->push($this->makeLabelField());
        }

        return $this->fields
            ->filter(function (Field $field) {
                return $field->hasFlag('table.show');
            })
            ->values()
            ->sortBy(function (Field $field, $key) {
                return $field->getConfigValue('sort_order', $key);
            });
    }
}