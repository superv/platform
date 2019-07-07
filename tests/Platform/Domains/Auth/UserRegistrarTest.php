<?php

namespace Tests\Platform\Domains\Auth;

use SuperV\Platform\Domains\Auth\UserRegistrar;
use Tests\Platform\TestCase;

class UserRegistrarTest extends TestCase
{
    function test__can_set_additional_rules()
    {
        $registar = app(UserRegistrar::class);
        $rules = $registar->rules();

        $registar->addRules([
            'first_name' => 'required',
            'last_name'  => 'required',
        ]);

        $this->assertEquals(array_merge($rules, [
            'first_name' => 'required',
            'last_name'  => 'required',
        ]), $registar->rules());
    }
}