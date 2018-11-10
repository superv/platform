<?php

namespace SuperV\Platform\Support;

class Config
{
    protected $data = [];

    public function set($key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get()
    {
        return $this->data;
    }
}