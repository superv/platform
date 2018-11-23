<?php

namespace SuperV\Platform\Support\Composer;

interface Composable
{
    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null);
}