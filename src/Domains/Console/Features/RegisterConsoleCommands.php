<?php

namespace SuperV\Platform\Domains\Console\Features;

use Illuminate\Console\Application as Artisan;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;

class RegisterConsoleCommands extends Feature
{
    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle()
    {
        $routesFile = base_path($this->droplet->getPath("routes/console.php"));

        if (file_exists($routesFile)) {
            $commands = require $routesFile;
            if (is_array($commands)) {
                Artisan::starting(function ($artisan) use ($commands) {
                    $artisan->resolveCommands($commands);
                });
            }
        }
    }
}