<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

class GetComposerConfig
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        if (! file_exists($this->path.'/composer.json')) {
            throw new \Exception("Composer file does not exist at {$this->path}/composer.json");
        }

        if (! $composer = json_decode(file_get_contents($this->path.'/composer.json'), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$this->path}/composer.json");
        }

        if (! array_key_exists('autoload', $composer)) {
            return false;
        }

        return $composer;
    }
}
