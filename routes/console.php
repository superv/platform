<?php

use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallSuperV;
use SuperV\Platform\Domains\Database\Migration\Console\MakeMigrationCommand;
use SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletReinstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletSeedCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletUninstallCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;

return [
    DropletInstallCommand::class,
    DropletUninstallCommand::class,
    DropletReinstallCommand::class,
    DropletSeedCommand::class,
    MakeMigrationCommand::class,
    MakeDropletCommand::class,
//    MigrateCommand::class,
    EnvSet::class,
    InstallSuperV::class,
];