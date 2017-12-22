<?php

namespace SuperV\Platform\Traits;

trait HasHandler
{
    /** @var string */
    protected $handler;

    /**
     * @param string $handler
     *
     * @return $this
     */
    public function setHandler(string $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @return bool
     */
    public function hasHandler()
    {
        return !is_null($this->handler);
    }

    public function newHandlerInstance()
    {
        return app($this->handler);
    }
}