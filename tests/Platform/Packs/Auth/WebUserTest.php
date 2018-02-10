<?php

namespace Tests\SuperV\Platform\Packs\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use SuperV\Platform\Packs\Auth\User;
use SuperV\Platform\Packs\Auth\Users;
use SuperV\Platform\Packs\Auth\WebUsers;
use SuperV\Platform\Packs\Nucleo\Blueprint;
use Tests\SuperV\Platform\BaseTestCase;

class WebUserTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function if_the_webuser()
    {
        $webUser = app(WebUsers::class)->create([
            'first_name' => 'Abou',
            'last_name'  => 'Yousef',
            'user'       => [
                'email'    => 'ay@superv.io',
                'password' => 'secret',
            ],
        ]);

        $this->assertNotNull($webUser->user);
        $this->assertEquals(app(Users::class)->first(), $webUser->user);
    }


    function webuser_structs()
    {
        $this->builder()->table('users_web', function(Blueprint $table) {
            $table->string('phone')->nullable()->rules('required|min:3')->scatter();
        });

        try {
            $webUser = app(WebUsers::class)->create([
                'first_name' => 'Abou',
                'last_name'  => 'Yousef',
                'phone'      => '231',
                'user'       => [
                    'email'    => 'ay@superv.io',
                    'password' => 'secret',
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
            dd($e->errors());
        }

        dd($webUser->toArray());

    }


    /**
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    protected function builder()
    {
        $builder = \DB::getSchemaBuilder();
        $builder->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}