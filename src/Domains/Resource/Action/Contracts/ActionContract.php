<?php

namespace SuperV\Platform\Domains\Resource\Action\Contracts;

interface ActionContract
{
    public function getName();

    public function getTitle();

    public function compose(): array;

}