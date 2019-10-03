<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormValidatingHook;

class OrdersFormDefault implements FormResolvedHook, FormValidatingHook
{
    public static $identifier = 'testing.orders.forms:default';

    public function resolved(FormInterface $form)
    {
        $_SERVER['__hooks::form.resolved'] = $form->getIdentifier();
//        $form->onlyFields('number', 'status');
    }

    public function validating(FormInterface $form)
    {
        $_SERVER['__hooks::form.validating'] = $form;
//        $form->getField('status')->removeRules();
    }
}
