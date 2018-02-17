<?php

namespace Tests\SuperV\Platform\Domains\Auth;

use SuperV\Platform\Domains\Auth\UserRegistrar;
use Tests\SuperV\Platform\BaseTestCase;

class UserRegistrarTest extends BaseTestCase
{
    /** @test */
    function can_set_additional_rules()
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