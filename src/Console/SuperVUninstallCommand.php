<?php

namespace SuperV\Platform\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;

class SuperVUninstallCommand extends Command
{
    protected $signature = 'superv:uninstall';

    protected $description = 'Uninstall SuperV Platform';

    public function handle(AddonCollection $addons)
    {
        $this->comment('Uninstalling SuperV');

        $addons->map(function (Addon $addon) {
            if ($addon->slug() === 'platform') {
                return;
            }
            $this->comment('Uninstalling addon: ['.$addon->slug().']');
            UninstallAddonJob::dispatch($addon);
        });

        $this->call('migrate:rollback', ['--addon' => 'platform', '--force' => true]);

        $this->setEnv('SV_INSTALLED=false');

        $this->comment("SuperV Uninstalled..! \n");
    }

    public function setEnv($line)
    {
        list($variable, $value) = explode('=', $line, 2);

        $data = $this->readEnvironmentFile();

        array_set($data, $variable, $value);

        $this->writeEnvironmentFile($data);
    }

    protected function readEnvironmentFile()
    {
        $data = [];

        $file = base_path('.env');

        if (! file_exists($file)) {
            return $data;
        }

        foreach (file($file) as $line) {
            // Check for # comments.
            if (starts_with($line, '#')) {
                $data[] = $line;
            } elseif ($operator = strpos($line, '=')) {
                $key = substr($line, 0, $operator);
                $value = substr($line, $operator + 1);

                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function writeEnvironmentFile($data)
    {
        $contents = '';

        foreach ($data as $key => $value) {
            if ($key) {
                $contents .= PHP_EOL.strtoupper($key).'='.$value;
            } else {
                $contents .= PHP_EOL.$value;
            }
        }

        $file = base_path('.env');

        file_put_contents($file, $contents);
    }
}