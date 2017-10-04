<?php

namespace SuperV\Platform\Traits;

use SuperV\Platform\Domains\Entry\EntryModel;

trait HasEntry
{
    /** @var  EntryModel */
    protected $entry;

    public function __call($method, $arguments)
    {
        if ($this->entry) {
            return $this->entry->getAttribute($method);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    public function __get($name)
    {
        if ($this->entry) {
            return $this->entry->getAttribute($name);
        }
    }

    /**
     * @param EntryModel $entry
     *
     * @return $this
     */
    public function setEntry(EntryModel $entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * @return EntryModel
     */
    public function getEntry()
    {
        return $this->entry;
    }
}