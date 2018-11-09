<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;

class FieldFactory
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

    /**
     * @var array
     */
    protected $params;

    protected function create(array $params): Field
    {
        $field = Field::make($params);

        return $field;
    }

    public function fromType(FieldType $fieldType): Field
    {
        $params = [
            'name' => $fieldType->getName(),
            'type' => $fieldType->getType(),
        ];


        return $this->create($params);
    }

    public function fromEntry(FieldModel $fieldEntry): Field
    {
        return $this->create($fieldEntry->toArray());
    }

    public static function createFromEntry(FieldModel $entry): Field
    {
        return (new static)->fromEntry($entry);
    }

}