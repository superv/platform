<?php

use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletReinstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletSeedCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletUninstallCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;

return [
    EnvSet::class,
    InstallCommand::class,
    DropletInstallCommand::class,
    DropletSeedCommand::class,
    DropletUninstallCommand::class,
    DropletReinstallCommand::class,
    MakeDropletCommand::class,
];