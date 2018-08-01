<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = [];

    protected $table = 'nucleo_fields';

    public $timestamps = false;

    protected $casts = [
        'required' => 'boolean',
        'rules'    => 'array',
        'config'   => 'json',
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

    public function getRules()
    {
        return array_filter(array_merge($this->rules, $this->required ? ['required'] : []));
    }

    public function hasRules()
    {
        return ! empty($this->getRules());
    }

    public function label()
    {
        return ucwords(str_replace('_', ' ', $this->slug));
    }
}