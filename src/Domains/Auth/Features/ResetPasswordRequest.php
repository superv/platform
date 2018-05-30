<?php

namespace SuperV\Platform\Domains\Auth\Features;


use Illuminate\Support\Facades\Hash;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Feature\AbstractFeatureRequest;

class ResetPasswordRequest extends AbstractFeatureRequest
{
    public function make()
    {
        $email = $this->getParam('email');
        $token = $this->getParam('code');

        $row = \DB::table('password_resets')->where('email', $email)->first();

        if (!$row) {
            $this->throwValidationError('Reset request not found or expired');
        }

        if (!Hash::check($token, $row->token)) {
            $this->throwValidationError('Invalid code');
        }

        $this->validate($this->params, [ 'password' => 'required|confirmed|min:6']);

        \DB::table('password_resets')->where('email', $email)->delete();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->feature->setParam('user', $user);
        $this->transfer('password');
    }
}