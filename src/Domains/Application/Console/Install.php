<?php

namespace SuperV\Platform\Domains\Application\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class Install extends Command
{
    protected $signature = 'superv:install';

    protected $description = '';

    public function handle(Droplets $droplets,  Kernel $kernel)
    {
        $kernel->call('migrate', ['--force' => true]);

        $kernel->call('migrate', ['--force' => true, '--path' => 'vendor/superv/platform/database/migrations']);

        $droplets->create([
            'id'        => 1,
            'name'      => 'platform',
            'vendor'    => 'superv',
            'slug'      => 'superv.platform',
            'namespace' => 'SuperV\Platform',
            'path'      => 'vendor/superv/platform',
            'type'      => 'module',
            'enabled'   => false,
        ]);

        $kernel->call('env:set', ['line' => 'SUPERV_INSTALLED=true']);

        $kernel->call('droplet:install', [
            'slug'   => 'superv.modules.auth',
            '--path' => 'droplets/superv/modules/auth',
        ]);

        $kernel->call('droplet:install', [
            'slug'   => 'superv.ports.acp',
            '--path' => 'droplets/superv/ports/acp',
        ]);

        $kernel->call('droplet:install', [
            'slug'   => 'superv.modules.supreme',
            '--path' => 'droplets/superv/modules/supreme',
        ]);

        $kernel->call('droplet:install', [
            'slug'   => 'superv.modules.hosting',
            '--path' => 'droplets/superv/modules/hosting',
        ]);
    }
}