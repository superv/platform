<?php

namespace Tests\Platform\Domains\Resource\Features;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Features\UserType;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class UserTypeTest
 *
 * @package Tests\Platform\Domains\Resource\Features
 * @group   resource
 */
class UserTypeTest extends ResourceTestCase
{
    function test__adds_user_field()
    {
        $managers = $this->create('tbl_managers',
            function (Blueprint $table, Config $config) {
                $config->setIdentifier('testing.managers');
                $config->hasUserWithRole('manager');

                $table->increments('id');
                $table->string('name')->entryLabel();
            });

        $userField = $managers->getField('user');
        $this->assertNotNull($userField);
        $this->assertTrue($userField->hasFlag('static'));

        $this->assertEquals([
            'bind'      => ['name'],
            'params'    => ['role' => 'manager'],
            'on_create' => UserType::class,
        ], $userField->getConfigValue('inline'));
    }

    function test__assigns_role_to_user_when_created()
    {
        $this->withoutExceptionHandling();

        $managers = $this->create('tbl_managers',
            function (Blueprint $table, Config $config) {
                $config->setIdentifier('testing.managers');
                $config->hasUserWithRole('manager');

                $table->increments('id');
                $table->string('name')->entryLabel();
            });

        $response = $this->postCreateResource($managers, [
            'name' => 'The Man',
            'user' => [
                'email'    => 'the@man.com',
                'password' => 'secret',
            ],
        ]);

        $response->assertOk();
        $entry = $managers->first();

        /** @var \SuperV\Platform\Domains\Auth\Contracts\User $user */
        $user = $entry->load('user')->user;

        $this->assertTrue($user->isA('user'));
        $this->assertTrue($user->isA('manager'));
    }
}
