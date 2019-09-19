<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Jobs;

use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Support\Composer\Payload;

class RenderComponent
{
    public function handle(Payload $props)
    {
        return Component::make('sv-form-v2')->setProps($props);
    }

    /**
     * @return static
     */
    public static function resolve()
    {
        return app(static::class);
    }
}
