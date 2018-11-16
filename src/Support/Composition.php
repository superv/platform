<?php

namespace SuperV\Platform\Support;

use SuperV\Platform\Contracts\Arrayable;

/**
 * Holds the composed data
 *
 * @package SuperV\Platform\Support
 */
class Composition implements Arrayable
{
    /**
     * @var array|null
     */
    protected $params;

    public function __construct(?array $params = [])
    {
        $this->params = array_filter_null($params);
    }

    public function replace($key, $value)
    {
        array_set($this->params, $key, $value);
    }

    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->params;
        }

        return array_get($this->params, $key);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->get();
    }
}