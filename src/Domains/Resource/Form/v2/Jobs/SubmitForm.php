<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Jobs;

use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;

class SubmitForm
{
    public function handle(Form $form, array $data = [])
    {
    }

    /**
     * @return static
     */
    public static function resolve()
    {
        return app(static::class);
    }
}
