<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use Platform;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Support\Path;

class RegisterExtensions
{
    public function handle()
    {
        // Register platform resources before boot
        // so that addons can override them
        //
        RegisterExtensionsInPath::dispatch(Platform::path('src/Extensions'), 'SuperV\Platform\Extensions');
    }

    public function getClassName($file)
    {
        return str_replace('.php', '', basename($file));
    }

    protected function registerResources()
    {
        $this->addons->map(function (Addon $addon) {
            $folder = $addon->realPath('src/Resources');

            if (! file_exists($folder)) {
                return;
            }

            $searchIn = array_merge(
                glob($folder.'/*Resource.php'),
                glob($folder.'/**/*Resource.php')
            );

            /**
             * register resources and navigations
             */
            if (! empty($searchIn)) {
                foreach ($searchIn as $file) {
                    $resourceClass = Path::parseClass($addon->namespace(), $addon->realPath('src'), $file);
                    $this->registerNav($resourceClass);
                    Nucleo::resourceMap([$resourceClass]);
                }
            }
        });
    }
}