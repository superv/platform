<?php

namespace SuperV\Platform\Console\Jobs;

use Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as SchemaBuilder;
use Schema;

class InstallSuperVJob
{
    public function handle()
    {
        if (! SchemaBuilder::hasTable('migrations')) {
            Artisan::call('migrate', ['--force' => true]);
        }

        if (! SchemaBuilder::hasColumn('migrations', 'scope')) {
            Schema::table('migrations', function (Blueprint $table) {
                $table->string('scope')->nullable();
            });
        }

        Artisan::call('migrate', ['--scope' => 'platform', '--force' => true]);

        Artisan::call('vendor:publish', ['--tag' => 'superv.config']);
        Artisan::call('vendor:publish', ['--tag' => 'superv.views']);
        Artisan::call('vendor:publish', ['--tag' => 'superv.assets']);

        $this->setEnv('SV_INSTALLED=true');
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