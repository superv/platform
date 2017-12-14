<?php

use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallSuperV;
use SuperV\Platform\Domains\Database\Migration\Console\DropletMigrationCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletReinstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletSeedCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletUninstallCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;

return [
    DropletUninstallCommand::class,
    DropletReinstallCommand::class,
    DropletSeedCommand::class,
    DropletMigrationCommand::class,
    MakeDropletCommand::class,
];