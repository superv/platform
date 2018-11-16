<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Watcher;

class FormConfig
{
    protected $uuid;

    protected $url;

    protected $groups = [];

    protected $hiddenFields = [];

    protected $sleeping = false;

    protected function __construct()
    {
        $this->boot();
    }

    protected function boot()
    {
        $this->uuid = uuid();
        $this->url = sv_url('sv/forms/'.$this->uuid);
    }

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        $this->groups[$handle] = ['provider' => $fieldsProvider, 'watcher' => $watcher];

        return $this;
    }

    public function sleep()
    {
        cache()->forever($this->cacheKey($this->uuid()), serialize($this));

        $this->sleeping = true;

        return $this;
    }

    public function hideField(string $fieldName): self
    {
        $this->hiddenFields[] = $fieldName;

        return $this;
    }

    public function makeForm(): Form
    {
        if (! $this->sleeping) {
            $this->sleep();
        }

        return Form::make($this);
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function cacheKeyPrefix()
    {
        return 'sv:forms';
    }

    public static function cacheKey(string $uuid): string
    {
        return static::cacheKeyPrefix().':'.$uuid;
    }

    public static function make(): FormConfig
    {
        return new static;
    }

    public static function wakeup($uuid): ?self
    {
        /** @var \SuperV\Platform\Domains\Resource\Form\FormConfig $config */
        if ($config = cache(static::cacheKey($uuid))) {
            return unserialize($config);
        }

        return null;
    }
}