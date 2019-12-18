<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
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

    public function getField($handle): FieldInterface
    {
        $field = $this->fields->first(
            function (FieldInterface $field) use ($handle) {
                return $field->getHandle() === $handle;
            });

        if (! $field) {
            PlatformException::fail("Field not found: [{$handle}]");
        }

        return $field;
    }

    public function hide($handle)
    {
        $field = $this->getField($handle);
        $field->removeFlag('table.show');

        return $field;
    }

    public function showFirst($handle, $label = null)
    {
        return $this->show($handle, $label)->displayOrder(-999);
    }

    public function showLast($handle, $label = null)
    {
        return $this->show($handle, $label)->displayOrder(+999);
    }

    public function show($handle, $label = null)
    {
        $field = $this->getField($handle);
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

    public function get($handle = null)
    {
        if ($handle) {
            return $this->getField($handle);
        }

        $fields = $this->fields
            ->filter(function (FieldInterface $field) {
                return $field->hasFlag('table.show') || $field->getHandle() === $this->resource->config()->getEntryLabelField();
            })
            ->values()
            ->sortBy(function (FieldInterface $field, $key) {
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
            'handle'     => 'label',
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
