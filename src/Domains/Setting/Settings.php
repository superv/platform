<?php

namespace SuperV\Platform\Domains\Setting;


class Settings extends JSON
{
    // -----------------------------------------------------------------------------------------------------------------
    // properties

    // -----------------------------------------------------------------------------------------------------------------
    // instantiation

    public function __construct()
    {
        parent::__construct(storage_path('superv/settings.json'));
    }


    // -----------------------------------------------------------------------------------------------------------------
    // methods

    public function save($data)
    {
        $this->data = $data;
        $this->create();
    }
}