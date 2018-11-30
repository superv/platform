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

    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->filterNull ? array_filter($this->params) : $this->params;
        }

        return array_get($this->params, $key);
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

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->get();
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        return $this->toArray();
    }
}