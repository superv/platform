<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface ProvidesFieldComponent
{
    public function getComponentName(): string;
}