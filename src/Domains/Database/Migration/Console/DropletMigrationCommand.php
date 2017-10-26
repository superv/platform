<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DropletMigrationCommand extends Command
{
    protected $signature = 'droplet:migration {droplet} {name} {--create}';

    public function handle(Droplets $droplets, Kernel $kernel, DropletFactory $factory)
    {
        if (! $droplet = $factory->fromSlug($this->argument('droplet'))) {
            throw new \InvalidArgumentException("Droplet [{$this->argument('droplet')} not found]");
        }

        $options = [
            '--droplet' => $droplet->getSlug()
        ];

        $name = $this->argument('name');
        if ($this->option('create')) {
            $table = "{$droplet->getName()}_{$name}";
            array_set($options, '--create', $table);
            array_set($options, 'name', "create_{$table}_table");
        } else {
            array_set($options, 'name', $name);
        }

        $kernel->call('make:migration', $options, $this->output);
    }
}
