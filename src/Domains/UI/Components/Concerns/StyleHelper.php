<?php

namespace SuperV\Platform\Domains\UI\Components\Concerns;

trait StyleHelper
{
    /**
     * CSS methods
     */

    /**
     * Set margin right
     *
     * @param $value
     * @return static
     */
    public function mR($value): self
    {
        return $this->addClass("mr-{$value}");
    }

    /**
     * Set width
     *
     * @param $value
     * @return static
     */
    public function w($value): self
    {
        return $this->addClass("w-{$value}");
    }

    /**
     * Set padding
     *
     * @param $value
     * @return self
     */
    public function p($value): self
    {
        return $this->addClass("p-{$value}");
    }

    public function card(): self
    {
        return $this->addClass('sv-card');
    }
}