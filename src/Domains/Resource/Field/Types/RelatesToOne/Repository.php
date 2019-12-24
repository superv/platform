<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

class Repository
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface */
    protected $field;

    public function setField(\SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field): Repository
    {
        $this->field = $field;

        return $this;
    }
}