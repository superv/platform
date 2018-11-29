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

    public function showOnIndex(): Field
    {
        return $this->addFlag('table.show');
    }

    public function hide()
    {
        return $this->addFlag('hidden');
    }

    public function isHidden(): bool
    {
        return $this->hasFlag('hidden');
    }

    public function isUnique()
    {
        return $this->hasFlag('unique');
    }

    public function isRequired()
    {
        return $this->hasFlag('required');
    }

    public function isFilter()
    {
        return $this->hasFlag('filter');
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    public function doesNotInteractWithTable()
    {
        return $this->doesNotInteractWithTable;
    }

}