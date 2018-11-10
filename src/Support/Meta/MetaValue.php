<?php

namespace SuperV\Platform\Support\Meta;

class MetaValue
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function getValue($key = null)
    {
        if (is_null($key)) {
            return $this->value;
        }

        return $this->value[$key];
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $value = new Meta($value);
        }
        $this->value = $value;
    }

    public function push($value)
    {
        $this->value[] = $value;
    }

    public function get(string $key)
    {
        $keys = explode('.', $key);
        if (count($keys) === 1) {
            return $this->getValue($key);
        } elseif ($data = $this->value[array_shift($keys)]) {
            $subKey = implode('.', $keys);
            if ($data instanceof Meta) {
                return $data->get($subKey);
            } elseif (is_array($data)) {
                return array_get($data, $subKey);
            }

            return $data;
        }
    }
}