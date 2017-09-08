<?php

namespace SuperV\Platform\Domains\Application\Console;

use Collective\Remote\Connection;
use Collective\Remote\RemoteManager;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Modules\Supreme\Domains\Server\Jobs\RunServerScript;
use SuperV\Modules\Supreme\Domains\Server\Model\AccountModel;
use SuperV\Modules\Supreme\Domains\Server\Model\ServerModel;
use SuperV\Modules\Supreme\Domains\Server\Server;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class InstallSuperV extends Command
{
    use DispatchesJobs;

    protected $signature = 'superv:install';

    protected $description = '';

    public function handle(Droplets $droplets,  Kernel $kernel)
    {
        $this->alert('SuperV Setup');
        /** @var RemoteManager $ssh */
        $ssh = app('remote');

        /** @var Connection $ssh */
        $ssh = $ssh->connect(['host' => '192.168.5.10', 'username' => 'root', 'keytext' => pkey(), 'keyphrase' => '']);

        $ssh->run(['pwd', 'apt5-get upgrade'], function($line) {
            $this->comment($line);
        });

        $this->comment($ssh->status());

//
//        $choice = $this->confirm('Will we setup SuperV on a remote server?', true);
//
//        $serverIp = $this->ask('Enter server ip address', '192.168.5.10');
//        $sshKey = $this->secret('Enter your ssh private key');
//
//        $this->comment($sshKey);

//        $this->postInstall($droplets, $kernel);
    }

    /**
     * @param Droplets $droplets
     * @param Kernel   $kernel
     */
    protected function postInstall(Droplets $droplets, Kernel $kernel): void
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