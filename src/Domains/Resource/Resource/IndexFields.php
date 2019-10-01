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

        // Lets add this flag here to mark that
        // the field is added later on
        //
        $field->addFlag('unbound');

        $this->fields->push($field);

        return $field;
    }

    public function hideLabel()
    {
        $this->hideLabelField = true;

        return $this;
    }

    public function get($name = null)
    {
        if ($name) {
            return $this->getField($name);
        }

        $fields = $this->fields
            ->filter(function (Field $field) {
                return $field->hasFlag('table.show') || $field->getName() === $this->resource->config()->getEntryLabelField();
            })
            ->values()
            ->sortBy(function (Field $field, $key) {
                return $field->getConfigValue('sort_order', $key);
            });

        $entryLabelField = $this->resource->fields()->getEntryLabelField();
        if ($entryLabelField && $this->hideLabelField === false && $fields->isEmpty()) {
            $fields->push($entryLabelField);
        }

        if (! $entryLabelField) {
            $fields->push($this->makeLabelField());
        }

        return $fields->filter();
    }

    protected function makeLabelField()
    {
        $fieldParams = [
            'identifier' => $this->resource->getIdentifier().'.fields:entry_label',
            'type'       => 'text',
            'name'       => 'label',
            'label'      => $this->resource->getSingularLabel(),
        ];

        return FieldFactory::createFromArray($fieldParams)
                           ->setCallback('table.presenting',
                               function (EntryContract $entry) {
                                   return sv_parse(
                                       $this->resource->config()->getEntryLabel(),
                                       $entry->toArray()
                                   );
                               })
                           ->showOnIndex()
                           ->displayOrder(-1);
    }
}
