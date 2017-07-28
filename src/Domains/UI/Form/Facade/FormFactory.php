<?php namespace SuperV\Platform\Domains\UI\Form\Facade;

use Illuminate\Support\Facades\Facade;
use Symfony\Component\Form\FormFactoryInterface;

class FormFactory extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return FormFactoryInterface::class;
    }
}
