<?php

namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Contracts\View\Factory;
use SuperV\Platform\Domains\Droplet\Port\Port;

class ActivatePort
{
    /**
     * @var Port
     */
    protected $port;

    public function __construct(Port $port)
    {
        $this->port = $port;
    }

    public function handle(Factory $view)
    {
        app()->instance(Port::class, $this->port);

        if ($themeSlug = $this->port->getTheme()) {
            if ($theme = superv('droplets')->bySlug($themeSlug)) {
                $view->addNamespace('theme', [base_path($theme->getPath('resources/views'))]);
                superv('assets')->addPath('theme', $theme->getPath('resources'));
            }
        }

        $view->addNamespace('port', [base_path($this->port->getPath('resources/views'))]);
    }
}