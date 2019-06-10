<?php

namespace SuperV\Platform\Domains\Resource\Field\Rules;

use Illuminate\Contracts\Validation\Rule;

class HumanNameRule implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^[A-Za-z\s\-]+$/u', $value);
    }

    public function message()
    {
        return trans('validation.human_name');
    }
}