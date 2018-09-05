<?php

namespace SuperV\Platform\Console;

use Illuminate\Console\Command;

class SuperVInstallCommand extends Command
{
    protected $signature = 'superv:install';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->comment('Installing SuperV');
        $this->call('migrate', ['--scope' => 'platform', '--force' => true]);

        $this->setEnv();

        $this->comment("SuperV installed..! \n");
    }

    protected function setEnv()
    {
        $file = base_path('.env');
        $lines = file_exists($file) ? file($file) : [];

        $envParam = 'SV_INSTALLED';
        $done = false;
        foreach ($lines as &$line) {
            $line = trim($line);
            if (starts_with($line, $envParam)) {
                $done = (bool)($line = "{$envParam}=true");
                break;
            }
        }

        if (! $done) {
            array_push($lines, "{$envParam}=true");
        }

        file_put_contents($file, implode("\r\n", $lines));
    }
}