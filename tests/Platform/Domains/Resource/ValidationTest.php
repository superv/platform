<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Exceptions\ValidationException;

class ValidationTest extends ResourceTestCase
{
    /** @test */
    function runs_validation_when_saving_resource_entry()
    {
        $res = $this->makeResource('t_users', ['name', 'age:integer']);
        $res->build();

        $user = $res->create(['name' => 'Nicola']);

        $this->expectException(ValidationException::class);

        $user->save();

    }
}