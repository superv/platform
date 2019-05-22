<?php

namespace Tests\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use SuperV\Platform\Domains\Auth\Contracts\Users;
use SuperV\Platform\Domains\Auth\Events\UserCreatedEvent;
use SuperV\Platform\Domains\Auth\User;
use Tests\Platform\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function users_can_register_with_valid_credentials()
    {
        $this->withoutExceptionHandling();

        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->post('register', [
            'name'                  => 'User Me',
            'email'                 => 'user@example.com',
            'password'              => 'secret',
            'password_confirmation' => 'secret',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect((new ControllerStub)->getRedirectTo());

        $user = app(Users::class)->first();
        $this->assertEquals('user@example.com', $user->email);
        $this->assertTrue(\Hash::check('secret', $user->password));
    }

    /**
     * @test
     */
    function dispatches_an_event_upon_successfull_registration()
    {
        Event::fake();
        app('router')->post('register', ControllerStub::class.'@register');

        $this->post('register', $this->validParams());

        $user = app(Users::class)->first();

        Event::assertDispatched(UserCreatedEvent::class,
            function ($event) use ($user) {
                return $event->user->id === $user->id;
            });
    }

    /**
     * @test
     */
    function email_is_required()
    {
        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams(['email' => ''])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['email']);

        $this->assertEquals(0, app(Users::class)->count());
    }

    /**
     * @test
     */
    function email_must_be_a_valid_email_address()
    {
        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams(['email' => 'not_a_valid_email'])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['email']);

        $this->assertEquals(0, app(Users::class)->count());
    }

    /**
     * @test
     */
    function email_must_be_unique()
    {
        $this->newUser(['email' => 'client@example.com']);

        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams(['email' => 'client@example.com'])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['email']);

        $this->assertEquals(1, app(Users::class)->count());
    }

    /**
     * @test
     */
    function password_is_required()
    {
        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams(['password' => null])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['password']);

        $this->assertEquals(0, app(Users::class)->count());
    }

    /**
     * @test
     */
    function password_must_be_confirmed()
    {
        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams(['password_confirmation' => null])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['password']);

        $this->assertEquals(0, app(Users::class)->count());
    }

    /**
     * @test
     */
    function password_length_must_be_at_least_6()
    {
        app('router')->post('register', ControllerStub::class.'@register');

        $response = $this->from('register')
                         ->post(
                             'register', $this->validParams([
                             'password'              => '12345',
                             'password_confirmation' => '12345',
                         ])
                         );

        $response->assertStatus(302);
        $response->assertRedirect('register');
        $response->assertSessionHasErrors(['password']);

        $this->assertEquals(0, app(Users::class)->count());
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'name'                  => 'User Name',
            'email'                 => 'user@example.com',
            'password'              => 'secret',
            'password_confirmation' => 'secret',
        ], $overrides);
    }
}

class ControllerStub
{
    use \SuperV\Platform\Domains\Auth\Concerns\RegistersUsers;
}