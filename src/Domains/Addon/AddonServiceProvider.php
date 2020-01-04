<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Providers\BaseServiceProvider;

class AddonServiceProvider extends BaseServiceProvider
{
    /**
     * @var \SuperV\Platform\Domains\Addon\Addon
     */
    protected $addon;

    public function setAddon(Addon $addon)
    {
        $this->addon = $addon;

        return $this;
    }

    public function addon()
    {
        return $this->addon;
    }

    public function register()
    {
        parent::register();

        $this->registerViewNamespaces();

        if (file_exists($file = $this->addon->realPath('config/service/listeners.php'))) {
            $this->registerListeners((array)require($file));
        }

        if ($this->app->runningInConsole()) {
            MigrationScopes::register($this->addon->getIdentifier(), base_path($this->addon->path('database/migrations')));
        }

        $this->addon->loadConfigFiles();
    }

    public function boot()
    {
        Router::resolve()->loadFromPath($this->addon->realPath('routes'));

        $this->loadTranslationsFrom($this->addon->realPath('resources/lang'), $this->addon()->getIdentifier());

        $this->loadJsonTranslationsFrom($this->addon->realPath('resources/lang'));
    }

    protected function registerViewNamespaces(): void
    {
        $this->addViewNamespaces([
            $this->addon->getIdentifier() =>
                [
                    base_path($this->addon->resourcePath('views')),
                    resource_path('vendor/'.$this->addon->getVendor().'/'.$this->addon->getIdentifier().'/views'),
                ],
        ]);
    }
}
