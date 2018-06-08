<?php

namespace SuperV\Platform\Domains\Auth\Features;

use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Feature\AbstractFeatureRequest;

class StartResetPasswordRequest extends AbstractFeatureRequest
{
    public function make()
    {
        $user = User::query()->where('email', $this->getParam('email'))->first();

        if (! $user) {
            $this->throwValidationError('Email could not be found');
        }

        $this->feature->setParam('user', $user);
    }
}