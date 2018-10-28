<?php

namespace SuperV\Platform\Support\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasEntry
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $entry;

    public function __call($method, $arguments)
    {
        if ($this->entry) {
            return call_user_func_array([$this->entry, $method], $arguments);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    public function __get($name)
    {
        if (! $this->entry) {
            return null;
        }

        return $this->entry->getAttribute($name);
    }

    public function hasEntry(): bool
    {
        return ! is_null($this->entry);
    }

    public function getEntry(): ?Model
    {
        return $this->entry;
    }

    public function setEntry(Model $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getEntryId()
    {
        return $this->entry ? $this->entry->getKey() : null;
    }
}