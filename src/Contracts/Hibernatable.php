<?php

namespace SuperV\Platform\Contracts;

use SuperV\Platform\Support\Composer\Composable;

interface Hibernatable extends Composable
{
    /**
     * Hibernate and return wakeup url
     *
     * @return string
     */
    public function hibernate(): string;
}