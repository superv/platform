<?php

namespace SuperV\Platform\Domains\Entry;

use Illuminate\Database\Eloquent\Model;

class EntryModel extends Model
{
    protected $guarded = [];

    public static $rules;

    public static function rulesSometimes()
    {
        return static::rules(true);
    }

    public static function rules($sometimes = false)
    {
        if ($sometimes) {
            return collect(static::$rules)->map(function ($rule) {
                return 'sometimes|'.$rule;
            })->all();
        }

        return static::$rules;
    }

}