<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'required' => 'boolean',
        'rules'    => 'array',
    ];

    public function addRule($rule)
    {
        $rules = (array)$this->getAttribute('rules');
        array_push($rules, $rule);

        return $this->setRules($rules);
    }

    public function setRulesAttribute($rules)
    {
        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->attributes['rules'] = json_encode($rules);
    }

    public function setRules($rules)
    {
        $this->setAttribute('rules', $rules);

        return $this;
    }

    public function hasRules()
    {
        return is_array($this->rules) && ! empty($this->rules);
    }
}