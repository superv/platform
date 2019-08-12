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
            MigrationScopes::register($this->addon->slug(), base_path($this->addon->path('database/migrations')));
        }

        $this->addon->loadConfigFiles();
    }

    public function boot()
    {
        app(Router::class)->loadFromPath($this->addon->realPath('routes'));

        $this->loadTranslationsFrom($this->addon->realPath('resources/lang'), $this->addon()->slug());

        $this->loadJsonTranslationsFrom($this->addon->realPath('resources/lang'));
    }

    protected function registerViewNamespaces(): void
    {
        $this->addViewNamespaces([
            $this->addon->slug() =>
                [
                    base_path($this->addon->resourcePath('views')),
                    resource_path('vendor/superv/'.$this->addon->shortSlug().'/views'),
                ],
        ]);
    }
}
