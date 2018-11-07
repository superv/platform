<?php

namespace SuperV\Platform\Domains\Resource\Field;

class FieldConfig
{
    protected $fieldName;

    protected $rules = [];

    protected $config = [];

    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public static function field($fieldName): self
    {
        return new static($fieldName);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function mergeRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function config(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}