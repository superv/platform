<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

trait FieldFlags
{
    public function addFlag(string $flag): Field
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function showOnIndex()
    {
        return $this->addFlag('table.show');
    }
}