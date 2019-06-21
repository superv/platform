<?php

namespace SuperV\Platform\Support\Composer;

use SuperV\Platform\Contracts\Arrayable;

/**
 * Holds the composed data
 *
 * @package SuperV\Platform\Support
 */
class Payload implements Arrayable, Composable
{
    /**
     * @var array|null
     */
    protected $params;

    /**
     * @var array|null
     */
    protected $tokens;

    protected $filterNull = true;

    public function __construct(?array $params = [])
    {
        $this->params = $params;
    }

    public function set($key, $value)
    {
        array_set($this->params, $key, $value);
    }

    public function remove($key)
    {
        array_forget($this->params, $key);
    }

    public function push($key, $value)
    {
        $parent = $this->get($key, []);
        $parent[] = $value;

        return $this->set($key, $parent);
    }

    public function merge(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->filterNull ? array_filter_null($this->params) : $this->params;
        }

        return array_get($this->params, $key, $default);
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function setFilterNull(bool $filterNull): Payload
    {
        $this->filterNull = $filterNull;

        return $this;
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        return $this->toArray();
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
