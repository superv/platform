<?php

namespace SuperV\Platform\Domains\Auth\Handlers;

use SuperV\Modules\Ui\Domains\Form\FormHandler;
use SuperV\Platform\Domains\Auth\Domains\User\Services\UserCreator;

class RegisterFormHandler extends FormHandler
{
    public function handle(UserCreator $creator)
    {
        $creator->setName($this->post->get('name'))
                ->setEmail($this->post->get('email'))
                ->setPassword($this->post->get('password'))
                ->create()
                ->getUser();

        return ['redirect' => route('auth::login')];
    }
}