<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormValidatingHook;

class OrdersFormDefault implements FormResolvingHook, FormResolvedHook, FormValidatingHook
{
    public static $identifier = 'sv.testing.orders.forms:default';

    public function resolving(FormInterface $form, FormFields $fields)
    {
        $_SERVER['__hooks::form.resolving'] = $form->getIdentifier();
    }

    public function resolved(FormInterface $form, FormFields $fields)
    {
        $_SERVER['__hooks::form.resolved'] = $form->getIdentifier();
    }

    public function validating(FormInterface $form)
    {
        $_SERVER['__hooks::form.validating'] = $form;
    }
}
