<?php namespace SuperV\Platform\Domains\Entry\Traits;

use SuperV\Platform\Domains\Entry\EntryPresenter;

trait PresentableTrait
{
    public function getPresenter()
    {
        $presenter = substr(get_class($this), 0, -5) . 'Presenter';

        if (class_exists($presenter)) {
            return app()->makeWith($presenter, ['object' => $this]);
        }

        return new EntryPresenter($this);
    }

    public function newPresenter()
    {
        return $this->getPresenter();
    }
}