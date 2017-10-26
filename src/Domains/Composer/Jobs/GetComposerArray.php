<?php

namespace SuperV\Platform\Domains\Composer\Jobs;

class GetComposerArray
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        if (! file_exists($file = $this->path.'/composer.json')) {
            throw new \Exception("Composer file does not exist at {$file} ");
        }

        if (! $composer = json_decode(file_get_contents($file), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$file}");
        }

        if (! array_get($composer, 'autoload')) {
            throw new \Exception("No autoload data was found in {$file} ");
        }

        return $composer;
    }
}
