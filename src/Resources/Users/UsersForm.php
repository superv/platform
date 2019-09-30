<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvedHook;

class UsersForm implements FormResolvedHook
{
    public static $identifier = 'platform.users.forms:default';

    public function resolved(Form $form)
    {
        $form->hideField('password');
        $form->hideField('remember_token');
        $form->hideField('deleted_at');
        $form->hideField('deleted_by');
    }
}