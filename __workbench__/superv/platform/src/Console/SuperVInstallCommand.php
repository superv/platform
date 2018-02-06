<?php

namespace SuperV\Platform\Console;

use Illuminate\Console\Command;

class SuperVInstallCommand extends Command
{
    protected $signature = 'superv:install';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->call('migrate');

        $this->setEnv();

        $this->comment('SuperV installed..!');
    }

    protected function setEnv()
    {
        $file = base_path('.env');
        $contents = str_replace('SUPERV_INSTALLED=false', 'SUPERV_INSTALLED=true', file_get_contents($file));
        file_put_contents($file, $contents);
    }
}