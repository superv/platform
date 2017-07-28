<?php namespace SuperV\Platform\Domains\UI\Form\Facade;

use SuperV\Platform\Domains\UI\Form\FormRenderer as RealFormRenderer;
use Illuminate\Support\Facades\Facade;

class FormRenderer extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return RealFormRenderer::class;
    }
}
