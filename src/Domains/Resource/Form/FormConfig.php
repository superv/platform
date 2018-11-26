<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Resource;

class FormConfig
{
    protected $groups = [];

    protected $hiddenFields = [];

    /** @var string */
    protected $url;

    public function __construct($fieldsProvider = null)
    {
        if ($fieldsProvider) {
            $this->addGroup($fieldsProvider);
        }
    }

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        if ($fieldsProvider instanceof EntryContract) {
            $watcher = $fieldsProvider;
            $fieldsProvider = Resource::of($fieldsProvider->getTable());
        }
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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url): FormConfig
    {
        $this->url = $url;

        return $this;
    }

    public static function make($fieldsProvider = null): FormConfig
    {
        return new static($fieldsProvider);
    }
}