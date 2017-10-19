<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DropletMigrateCommand extends Command
{
    protected $signature = 'droplet:migrate {droplet} {--refresh} {--rollback} {--seed}';

    public function handle(Droplets $droplets, Kernel $kernel)
    {
        if (! $droplet = $droplets->withSlug($this->argument('droplet'))) {
            throw new \InvalidArgumentException("Droplet [{$this->argument('droplet')} not found]");
        }

        $options = [
            '--path'  => $droplet->path.'/database/migrations',
            '--force' => true,
        ];

        if ($this->option('refresh') || $this->option('rollback')) {
            $kernel->call('migrate:rollback', $options, $this->output);
        }

        if (! $this->option('rollback')) {
            $kernel->call('migrate', $options, $this->output);
        }

        if ($this->option('seed')) {
            $kernel->call('droplet:seed', ['droplet' => $this->argument('droplet')]);
        }
    }
}
