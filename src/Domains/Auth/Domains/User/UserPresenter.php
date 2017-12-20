<?php namespace SuperV\Platform\Domains\Auth\Domains\User;

use SuperV\Platform\Domains\Entry\EntryPresenter;

class UserPresenter extends EntryPresenter
{
    public function presentName()
    {
        return $this->object->email . ' <<-- prs';
    }
}