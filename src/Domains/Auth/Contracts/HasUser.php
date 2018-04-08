<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface HasUser
{
    public function email();

    public function user();
}