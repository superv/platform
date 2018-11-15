<?php

namespace SuperV\Platform\Domains\Auth\Features;

use SuperV\Platform\Domains\Feature\AbstractFeature;

class ResetPassword extends AbstractFeature
{
    /** @var \SuperV\Platform\Domains\Auth\Contracts\User * */
    protected $user;

    protected $code;

    protected $password;

    public function run()
    {
        $this->user->updatePassword($this->password);
    }

    public function getResponseData()
    {
        return [
            'data' => [
            ],
        ];
    }
}