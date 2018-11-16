<?php

namespace SuperV\Platform\Domains\Resource\Action\Contracts;

use SuperV\Platform\Support\Composition;

interface ActionContract
{
    public function getName();

    public function getTitle();

    public function compose(): Composition;
}