<?php

namespace SuperV\Platform\Traits;

use SuperV\Platform\Domains\Entry\EntryModel;

trait HasEntry
{
    /** @var  EntryModel */
    protected $entry;

//    public function __call($method, $arguments)
//    {
//        if ($this->entry) {
//            return call_user_func_array([$this->entry, $method], $arguments);
//        }
//
//        throw new \BadMethodCallException("Method {$method} does not exist.");
//    }
//
//    public function __get($name)
//    {
//        if ($this->entry) {
//            return $this->entry->getAttribute($name);
//        }
//    }

    /**
     * @return bool
     */
    public function hasEntry()
    {
        return !is_null($this->entry);
    }

    /**
     * @return EntryModel
     */
    public function getEntry()
    {
        return $this->entry;
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

    public function getEntryId()
    {
        return $this->entry ? $this->entry->getKey() : null;
    }
}