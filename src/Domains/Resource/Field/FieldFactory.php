<?php

namespace SuperV\Platform\Domains\Resource\Field;


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

    protected function create(): Field
    {
        return Field::make($this->params);
    }

    public function fromEntry(FieldModel $fieldEntry): Field
    {
        $this->params = $fieldEntry->toArray();
        return $this->create();
    }

    public static function createFromEntry(FieldModel $entry): Field
    {
        return (new static)->fromEntry($entry);
    }

}