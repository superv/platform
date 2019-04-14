<?php

namespace SuperV\Platform\Domains\Resource\Form;

class FormFactory
{
    public function create(array $attributes = [])
    {
        $formEntry = sv_resource('sv_forms')->create($attributes);

        return $formEntry;
    }
}