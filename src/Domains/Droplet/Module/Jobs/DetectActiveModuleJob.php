<?php namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DetectActiveModuleJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Droplets
     */
    private $droplets;

    public function __construct(Request $request, Droplets $droplets)
    {
        $this->request = $request;
        $this->droplets = $droplets;
    }

    public function handle()
    {
        /** @var Route $route */
        $route = $this->request->route();

        if (!$slug = array_get($route->getAction(), 'superv::droplet')) {
            return;
        }

        /** @var DropletModel $module */
        $module = $this->droplets->withSlug($slug);

        superv('view')->addNamespace(
            'module',
            [base_path($module->getPath('resources/views'))]
        );
    }
}