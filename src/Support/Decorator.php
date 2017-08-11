<?php namespace SuperV\Platform\Support;


use Robbo\Presenter\Decorator as BaseDecorator;

class Decorator extends BaseDecorator {

	/*
     * If this variable implements Robbo\Presenter\PresentableInterface then turn it into a presenter.
     *
     * @param  mixed $value
     * @return mixed $value
    */
    public function decorate($value)
    {
    	if (is_object($value) and isset($value->presenter))
    	{
    		$presenter = $value->presenter;
    		return new $presenter;
    	}

    	return parent::decorate($value);
    }
}