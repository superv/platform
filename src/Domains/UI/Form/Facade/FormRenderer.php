<?php

namespace SuperV\Platform\Domains\UI\Form\Facade;

use Illuminate\Support\Facades\Facade;
use SuperV\Platform\Domains\UI\Form\FormRenderer as RealFormRenderer;

class FormRenderer extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return RealFormRenderer::class;
    }
}
