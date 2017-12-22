<?php

namespace SuperV\Platform\Traits;

use SuperV\Platform\Support\Collection;

trait HasFields
{
    /** @var Collection */
    protected $fields;

    /**
     * @param Collection|array $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = is_array($fields) ? collect($fields) : $fields;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function getField($key)
    {
        return $this->fields->get($key);
    }

    /**
     * @param $key
     * @param $field
     *
     * @return $this
     */
    public function addField($key, $field)
    {
        $this->fields->put($key, $field);

        return $this;
    }
}