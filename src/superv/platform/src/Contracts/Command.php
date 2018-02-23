<?php

namespace SuperV\Platform\Contracts;

use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Command extends \Illuminate\Console\Command
{
    use DispatchesJobs;
}
