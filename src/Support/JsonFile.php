<?php

namespace SuperV\Platform\Support;

use Illuminate\Support\Arr;
use const JSON_PRETTY_PRINT;

class JsonFile
{
    protected $filePath;

    protected $jsonString;

    protected $data;

    public function __construct()
    {
    }

    public function get($key = null)
    {
        if ($key) {
            return array_get($this->data, $key);
        }

        return $this->data;
    }

    public function merge($key, $data = null)
    {
        if (is_null($data) && is_array($data = $key)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data[$key] = array_merge($this->data[$key], $data);
        }
    }

    public function write($targetPath = null)
    {
        if (! $targetPath) {
            $targetPath = $this->filePath;
        }

        $jsonString = json_encode($this->data, JSON_PRETTY_PRINT);
        $jsonString = str_replace('\/', '/', $jsonString);
        file_put_contents($targetPath, $jsonString);
    }

    public function remove($key)
    {
        Arr::forget($this->data, $key);
    }

    public static function fromPath(string $filePath): JsonFile
    {
        $self = static::resolve();
        $self->filePath = $filePath;
        $self->init();

        return $self;
    }

    public static function fromString(string $jsonString): JsonFile
    {
        $self = static::resolve();
        $self->jsonString = $jsonString;
        $self->init();

        return $self;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(JsonFile::class);
    }

    protected function init()
    {
        if (! $this->jsonString && $this->filePath) {
            $this->jsonString = file_get_contents($this->filePath);
        }

        $this->data = json_decode($this->jsonString, true);

        if (! $this->data) {
            throw new \InvalidArgumentException("Could not parse json. ".json_last_error_msg());
        }
    }
}