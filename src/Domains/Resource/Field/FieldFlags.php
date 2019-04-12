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

    public function removeFlag(string $flag): Field
    {
        $this->flags = array_diff($this->flags, [$flag]);

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

    public function hideOnForms(): Field
    {
        return $this->addFlag('form.hide');
    }

    public function hide()
    {
        return $this->addFlag('hidden');
    }

    public function isHidden(): bool
    {
        return (bool) $this->hasFlag('hidden');
    }

    public function isUnique()
    {
        return $this->hasFlag('unique');
    }

    public function isRequired()
    {
        return $this->hasFlag('required');
    }

    public function isUnbound()
    {
        return $this->hasFlag('unbound');
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
        return $this->fieldType instanceof DoesNotInteractWithTable;
    }

}