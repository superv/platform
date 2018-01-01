<?php

namespace SuperV\Platform\Domains\Application\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class InstallSuperV extends Command
{
    use DispatchesJobs;

    protected $signature = 'superv:install';

    protected $description = '';

    public function handle(Droplets $droplets, Kernel $kernel)
    {
        $kernel->call('env:set', ['line' => 'SUPERV_INSTALLED=false']);

        $kernel->call('migrate', ['--force' => true,
                                  '--path'  => 'vendor/superv/platform/database/migrations']);

//        $droplets->create([
//            'id'        => 1,
//            'name'      => 'platform',
//            'vendor'    => 'superv',
//            'slug'      => 'superv.platform',
//            'namespace' => 'SuperV\Platform',
//            'path'      => 'vendor/superv/platform',
//            'type'      => 'module',
//            'enabled'   => false,
//        ]);

        $kernel->call('env:set', ['line' => 'SUPERV_INSTALLED=true']);

        // Reload environment file
        foreach (file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            // Check for # comments.
            if (! starts_with($line, '#')) {
                putenv($line);
            }
        }

        $kernel->call('droplet:install', [
            'droplet' => 'superv.ports.acp',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/ports/acp',
        ]);

        $kernel->call('droplet:install', [
            'droplet' => 'superv.ports.web',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/ports/web',
        ]);

        $kernel->call('droplet:install', [
            'droplet' => 'superv.modules.ui',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/modules/ui',
        ]);

        $kernel->call('droplet:install', [
            'droplet' => 'superv.modules.nucleo',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/modules/nucleo',
            '--seed' => true
        ]);

        $kernel->call('droplet:install', [
            'droplet' => 'superv.themes.tailwind',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/themes/tailwind',
        ]);

        $kernel->call('droplet:install', [
            'droplet' => 'superv.themes.bulma',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/themes/bulma',
        ]);

        $this->comment("SuperV installed..!!!");
    }


//    public function handleNot(Droplets $droplets,  Kernel $kernel)
    //    {
    //        $this->alert('SuperV Setup');
    //        /** @var RemoteManager $ssh */
    //        $ssh = app('remote');
    //
    //        /** @var Connection $ssh */
    //        $ssh = $ssh->connect(['host' => '192.168.5.10', 'username' => 'root', 'keytext' => pkey(), 'keyphrase' => '']);
    //
    //        $ssh->run(['pwd', 'apt5-get upgrade'], function($line) {
    //            $this->comment($line);
    //        });
    //
    //        $this->comment($ssh->status());
    //
    ////
    ////        $choice = $this->confirm('Will we setup SuperV on a remote server?', true);
    ////
    ////        $serverIp = $this->ask('Enter server ip address', '192.168.5.10');
    ////        $sshKey = $this->secret('Enter your ssh private key');
    ////
    ////        $this->comment($sshKey);
    //
    ////        $this->postInstall($droplets, $kernel);
    //    }
}