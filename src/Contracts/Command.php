<?php

namespace SuperV\Platform\Contracts;

use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;

abstract class Command extends \Illuminate\Console\Command
{
    use ServesFeaturesTrait;
}
