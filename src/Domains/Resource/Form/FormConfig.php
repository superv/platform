<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\BaseConfig;
use SuperV\Platform\Domains\Resource\Field\Watcher;

class FormConfig extends BaseConfig
{
    protected $groups = [];

    protected $hiddenFields = [];

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        $this->groups[$handle] = ['provider' => $fieldsProvider, 'watcher' => $watcher];

        return $this;
    }


    public function hideField(string $fieldName): self
    {
        $this->hiddenFields[] = $fieldName;

        return $this;
    }

    public function makeForm(): Form
    {
        if (! $this->hibernating) {
            $this->hibernate();
        }

        return Form::make($this);
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    public static function make():FormConfig
    {
        return new static;
    }

    public static function getHandle(): string
    {
        return 'forms';
    }
}