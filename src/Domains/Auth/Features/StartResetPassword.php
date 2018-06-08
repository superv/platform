<?php

namespace SuperV\Platform\Domains\Auth\Features;

use App\Notifications\ResetPassword;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Feature\AbstractFeature;

class StartResetPassword extends AbstractFeature
{
    /** @var \SuperV\Platform\Domains\Auth\Contracts\User * */
    protected $user;

    public function run()
    {
        $table = \DB::table('password_resets');

        // delete old requests
        $table->where('email', $this->user->getEmail())->delete();

        $token = hash_hmac('sha256', Str::random(40), config('app.key'));
        $table->insert([
            'email'      => $this->user->getEmail(),
            'token'      => app('hash')->make($token),
            'created_at' => mysql_now(),
        ]);

        $this->user->notify(new ResetPassword($token));
    }
}