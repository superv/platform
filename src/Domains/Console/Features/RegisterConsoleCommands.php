<?php

namespace SuperV\Platform\Domains\Console\Features;

use Illuminate\Console\Application as Artisan;
use SuperV\Platform\Domains\Droplet\DropletServiceProviderInterface;
use SuperV\Platform\Domains\Feature\Feature;

class RegisterConsoleCommands extends Feature
{
    /**
     * @var DropletServiceProviderInterface
     */
    protected $provider;

    public function __construct(DropletServiceProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function handle()
    {
        if (file_exists($routesFile = base_path($this->provider->getPath("routes/console.php")))) {
            $commands = (array)require $routesFile;
        }

        $commands = array_merge($this->provider->getCommands(), $commands ?? []);

        if (is_array($commands)) {
            Artisan::starting(function ($artisan) use ($commands) {
                $artisan->resolveCommands($commands);
            });
        }
    }
}