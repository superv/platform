<?php

namespace SuperV\Platform\Domains\Resource\Field\Jobs;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Support\Dispatchable;

class AttachTypeToField
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType
     */
    protected $fieldType;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Field
     */
    protected $field;

    public function __construct(FieldType $fieldType, Field $field)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
    }

    public function handle()
    {
        if ($this->fieldType->hasAccessor()) {
            $this->field->on('accessing', $this->fieldType->getAccessor());
        }
        if ($presenting = $this->fieldType->getPresentingCallback()) {
            $this->field->on('presenting', $presenting);
        }

        $this->field->setVisible($this->fieldType->visible());

//                    ->setColumnName($this->fieldType->getColumnName())
//                    ->setHasDatabaseColumn($this->fieldType instanceof NeedsDatabaseColumn);
    }
}