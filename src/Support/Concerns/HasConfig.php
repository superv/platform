<?php

namespace SuperV\Platform\Support\Concerns;

trait HasConfig
{
    protected $config = [];

    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getConfig();
        }

        if (is_array($key)) {
            return $this->setConfig($key);
        }

        return $this->getConfigValue($key, $default);
    }

    public function getConfigValue($key, $default = null)
    {
        return array_get($this->getConfig(), $key, $default);
    }

    public function setConfigValue($key, $value = null)
    {
        if (! is_null($value)) {
            array_set($this->config, $key, $value);
        }

        return $this;
    }

    public function hasConfigValue($key): bool
    {
        return array_has($this->getConfig(), $key);
    }

    public function getConfig(): array
    {
        return $this->config ?: [];
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_replace_recursive($this->config, $config);

        return $this;
    }
}