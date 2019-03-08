<?php

namespace SuperV\Platform\Console;


use SuperV\Platform\Console\Jobs\InstallSuperVJob;
use SuperV\Platform\Contracts\Command;

class SuperVInstallCommand extends Command
{
    protected $signature = 'superv:install';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->comment('Installing SuperV');

        $this->dispatch(new InstallSuperVJob);

        $this->comment("SuperV installed..! \n");
    }
}