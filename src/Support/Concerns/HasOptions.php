<?php

namespace SuperV\Platform\Support\Concerns;

use Illuminate\Support\Collection;

trait HasOptions
{
    /** @var Collection */
    protected $options;

    /**
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(Collection $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options->put($key, $value);

        return $this;
    }

    /**
     * @param        $key
     * @param  null  $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->options->get($key, $default);
    }
}