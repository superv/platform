<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;

class OrdersFormDefault
{
    public static $identifier = 'testing::orders::forms.default';

    public function resolved(Form $form)
    {
        $_SERVER['__hooks::forms.default.resolved'] = [
            'form' => $form,
        ];
    }
}
