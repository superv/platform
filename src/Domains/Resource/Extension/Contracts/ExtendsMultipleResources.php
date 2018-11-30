<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

interface ExtendsMultipleResources
{
    /**
     * Pattern(s) to match the resources to be extended
     *
     * @return array|string
     */
    public function pattern();
}