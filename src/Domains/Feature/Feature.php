<?php

namespace SuperV\Platform\Domains\Feature;

interface Feature
{
    public function init();

    public function run();

    public function getResponseData();

    public function setParam($key, $value);
}