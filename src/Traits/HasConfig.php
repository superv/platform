<?php

namespace SuperV\Platform\Traits;

trait HasConfig
{
    public function config($key, $default = null)
    {
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

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }
}