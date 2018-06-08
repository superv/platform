<?php

namespace SuperV\Platform\Domains\Feature;

interface Request
{
    public function init($params);

    public function make();

    public function getParam($key);

    public function toArray();
}