<?php

namespace SuperV\Platform\Console\Jobs;

use SuperV\Platform\Support\Dispatchable;

class EnvFile
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $filePath;

    protected $merge = [];

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function set($key, $value)
    {
        $this->merge[$key] = $value."\n";

        return $this;
    }

    public function write()
    {
        $data = $this->readEnvironmentFile($this->merge, array_keys($this->merge));

        $this->writeEnvironmentFile($data);
    }

    public static function load($filePath): EnvFile
    {
        return (new static($filePath));
    }

    protected function readEnvironmentFile($data = [], $skipKeys = [])
    {
        $file = base_path('.env');

        if (! file_exists($file)) {
            return $data;
        }

        foreach (file($file) as $line) {
            // Check for # comments.
            if (starts_with($line, '#') || $line === "\n") {
                $data[] = $line;
            } elseif ($operator = strpos($line, '=')) {
                $key = substr($line, 0, $operator);

                if (in_array($key, $skipKeys)) {
                    continue;
                }
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
            if (is_string($key)) {
                $contents .= strtoupper($key).'='.$value;
            } else {
                $contents .= $value;
            }
        }

        $file = base_path('.env');

        file_put_contents($file, $contents);
    }
}