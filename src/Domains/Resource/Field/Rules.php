<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;

class Rules
{
    /**
     * @var array
     */
    protected $rules = [];

    public function merge(array $rules)
    {
        foreach ($rules as $rule) {
            $parts = explode(':', $rule);
            if (count($parts) === 1) {
                $this->rules[$parts[0]] = true;
            } else {
                $this->rules[$parts[0]] = $parts[1];
            }
        }

        return $this;
    }

    public function setRule($rule, $params): self
    {
        $this->rules[$rule] = $params;

        return $this;
    }

    public function get(array $params = [])
    {
        $rules = [];
        foreach ($this->rules as $key => $value) {
            if (is_bool($value)) {
                $rules[] = $key;
            } else {
                $rules[] = $key.':'. ($params ? sv_parse($value, $params) : $value);
            }
        }

        return $rules;
    }

    public static function of(FieldType $field)
    {
        return static::make($field->makeRules());

    }

    public static function make(array $rules): self
    {
        return (new static)->merge($rules);
    }
}