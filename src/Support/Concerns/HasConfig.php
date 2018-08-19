<?php

namespace SuperV\Platform\Support\Concerns;

trait HasConfig
{
    protected $config;

    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getConfig();
        }

        return $this->getConfigValue($key, $default);
    }

    public function getConfigValue($key, $default = null)
    {
        return array_get($this->config, $key, $default);
    }

    public function setConfigValue($key, $value)
    {
        array_set($this->config, $key, $value);

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasConfigValue($key)
    {
        return array_has($this->config, $key);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config ?: [];
    }
}