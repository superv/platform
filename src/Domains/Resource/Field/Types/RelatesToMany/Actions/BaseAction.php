<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Actions;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;

abstract class BaseAction extends Action
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $parentEntry;

    public function setField(FieldInterface $field): BaseAction
    {
        $this->field = $field;

        return $this;
    }

    public function setParentEntry(EntryContract $parentEntry): BaseAction
    {
        $this->parentEntry = $parentEntry;

        return $this;
    }
}