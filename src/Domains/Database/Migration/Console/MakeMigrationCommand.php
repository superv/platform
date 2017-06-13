<?php namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class MakeMigrationCommand extends Command
{
    protected $signature = 'droplet:migration {droplet} {name} {--create}';

    public function handle(Droplets $droplets, Kernel $kernel)
    {
        if (!$droplet = $droplets->withSlug($this->argument('droplet'))) {
            throw new \InvalidArgumentException("Droplet [{$this->argument('droplet')} not found]");
        }

        $options = [
            '--path' => $droplet->path . "/database/migrations",
        ];

        $name = $this->argument('name');
        if ($this->option('create')) {
            $table = "{$droplet->name}_{$name}";
            array_set($options, '--create', $table);
            array_set($options, 'name', "create_{$table}_table");
        } else {
            array_set($options, 'name', $name);

        }

        $kernel->call('make:migration', $options, $this->output);
    }
}