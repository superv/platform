<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface as Form;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvingHook;

class UsersForm implements FormResolvingHook
{
    public static $identifier = 'sv.platform.users.forms:default';

    public function resolving(Form $form, FormFields $fields)
    {
        $form->fields()->hide('remember_token');
        $form->fields()->hide('deleted_at');
        $form->fields()->hide('deleted_by');
    }
}