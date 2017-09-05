<?php

namespace SuperV\Platform\Domains\Application\Console;

use Illuminate\Console\Command;

class EnvSet extends Command
{
    protected $signature = 'env:set {line}';

    protected $description = 'Set an environmental value.';

    public function handle()
    {
        $line = $this->argument('line');

        list($variable, $value) = explode('=', $line, 2);

        $data = $this->readEnvironmentFile();

        array_set($data, $variable, $value);

        $this->writeEnvironmentFile($data);
    }

    protected function writeEnvironmentFile($data)
    {
        $contents = '';

        foreach ($data as $key => $value) {
            if ($key) {
                $contents .= strtoupper($key).'='.$value.PHP_EOL;
            } else {
                $contents .= $value.PHP_EOL;
            }
        }

        $file = base_path('.env');

        file_put_contents($file, $contents);
    }

    protected function readEnvironmentFile()
    {
        $data = [];

        $file = base_path('.env');

        if (! file_exists($file)) {
            return $data;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {

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
}