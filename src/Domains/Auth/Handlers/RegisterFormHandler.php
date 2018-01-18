<?php

namespace SuperV\Platform\Domains\Auth\Handlers;

use SuperV\Modules\Acp\Domains\User\Services\UserCreator;
use SuperV\Modules\Ui\Domains\Form\FormHandler;

class RegisterFormHandler extends FormHandler
{
    public function handle(UserCreator $creator)
    {
        $creator->setName($this->post->get('name'))
                ->setEmail($this->post->get('email'))
                ->setPassword($this->post->get('password'))
                ->create()
                ->getUser();

        return ['redirect' => route('acp::login')];
    }
}