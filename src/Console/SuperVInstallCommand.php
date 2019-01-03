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
        $this->call('migrate', ['--force' => true]);
        $this->call('migrate', ['--scope' => 'platform', '--force' => true]);

        $this->setEnv('SV_INSTALLED=true');

        $this->call('vendor:publish', ['--tag' => 'superv.config']);
        $this->call('vendor:publish', ['--tag' => 'superv.views']);
        $this->call('vendor:publish', ['--tag' => 'superv.assets']);

//        $this->call('jwt:secret', ['--force' => true]);

        $this->comment("SuperV installed..! \n");
    }

    public function setEnv($line)
    {
        list($variable, $value) = explode('=', $line, 2);

        $data = $this->readEnvironmentFile();

        array_set($data, PHP_EOL.$variable, $value);

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
                $contents .= strtoupper($key).'='.$value;
            } else {
                $contents .= PHP_EOL.$value;
            }
        }

        $file = base_path('.env');

        file_put_contents($file, $contents);
    }
}