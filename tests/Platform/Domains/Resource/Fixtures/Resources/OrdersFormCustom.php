<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;

class OrdersFormCustom
{
    public static $identifier = 'testing.orders.forms:custom';

    public function resolved(Form $form)
    {
        $_SERVER['__hooks::forms.resolved'] = [
            'form' => $form,
        ];
    }
}
