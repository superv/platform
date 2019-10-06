<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvedHook;

class UsersForm implements FormResolvedHook
{
    public static $identifier = 'platform.users.forms:default';

    public function resolved(FormInterface $form, FormFields $fields)
    {
        $form->fields()->hide('password');
        $form->fields()->hide('remember_token');
        $form->fields()->hide('deleted_at');
        $form->fields()->hide('deleted_by');
    }
}