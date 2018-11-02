<?php

namespace SuperV\Platform\Domains\Resource\Field;

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

    public function get()
    {
        $rules = [];
        foreach ($this->rules as $key => $value) {
            if (is_bool($value)) {
                $rules[] = $key;
            } else {
                $rules[] = $key.':'.$value;
            }
        }

        return $rules;
    }

    public static function make(array $rules): self
    {
        return (new static)->merge($rules);
    }
}