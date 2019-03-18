<?php

namespace SuperV\Platform\Console;


use Exception;
use SuperV\Platform\Console\Jobs\InstallSuperV;
use SuperV\Platform\Contracts\Command;

class SuperVInstallCommand extends Command
{
    protected $signature = 'superv:install';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->comment('Installing SuperV');

        try {
            app(InstallSuperV::class)();

            $this->comment("SuperV installed..! \n");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


    }
}