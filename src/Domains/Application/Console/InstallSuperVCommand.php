<?php

namespace SuperV\Platform\Domains\Application\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Console\Kernel;

class InstallSuperVCommand extends Command
{
    use DispatchesJobs;

    protected $signature = 'install';

    protected $description = '';

    public function handle(Kernel $kernel)
    {
        $kernel->call('migrate', [
            '--force' => true,
            '--path'  => 'vendor/superv/platform/database/migrations',
        ], $this->getOutput());

        $kernel->call('env:set', ['line' => 'SUPERV_INSTALLED=true'], $this->getOutput());

        reload_env();

        $kernel->call('droplet:install', [
            'droplet' => 'superv.ports.acp',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/ports/acp',
        ], $this->getOutput());

        $kernel->call('droplet:install', [
            'droplet' => 'superv.ports.web',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/ports/web',
        ], $this->getOutput());

        $kernel->call('droplet:install', [
            'droplet' => 'superv.modules.ui',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/modules/ui',
        ], $this->getOutput());

        $kernel->call('droplet:install', [
            'droplet' => 'superv.modules.nucleo',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/modules/nucleo',
            '--seed'  => true,
        ], $this->getOutput());

        $kernel->call('droplet:install', [
            'droplet' => 'superv.themes.tailwind',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/themes/tailwind',
        ], $this->getOutput());

        $kernel->call('droplet:install', [
            'droplet' => 'superv.themes.bulma',
            '--path'  => env('SUPERV_DROPLETS', 'droplets').'/superv/themes/bulma',
        ], $this->getOutput());

        $this->comment("SuperV installed..!!!");
    }


//    public function handleNot(Droplets $droplets,  Kernel $kernel)
    //    {
    //        $this->alert('SuperV Setup');
    //        /** @var RemoteManager $ssh */
    //        $ssh = app('remote');
    //
    //        /** @var Connection $ssh */
    //        $ssh = $ssh->connect(['host' => '192.168.5.10', 'username' => 'root', 'keytext' => pkey(), 'keyphrase' => ''], $this->getOutput());
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