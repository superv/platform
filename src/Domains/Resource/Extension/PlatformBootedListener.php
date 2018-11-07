<?php

namespace SuperV\Modules\Nucleo\Listeners;

use SuperV\Modules\Nucleo\Domains\Resource\Contracts\ResourcePage;
use SuperV\Modules\Nucleo\Nucleo;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Navigation\HasSection;
use SuperV\Platform\Domains\Navigation\SectionBag;
use SuperV\Platform\Support\Path;

class PlatformBootedListener
{
    /**
     * @var \SuperV\Platform\Domains\Addon\AddonCollection
     */
    protected $addons;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $events;

    public function __construct(AddonCollection $addons, Dispatcher $events)
    {
        $this->addons = $addons;
        $this->events = $events;
    }

    public function handle()
    {
        $this->registerResources();
        $this->registerPages();
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

    protected function registerPages()
    {
        $this->addons->map(function (Addon $addon) {
            $folder = $addon->realPath('src/Pages');

            if (! file_exists($folder)) {
                return;
            }

            $searchIn = array_merge(
                glob($folder.'/*Page.php'),
                glob($folder.'/**/*Page.php')
            );

            /**
             * register resources and navigations
             */
            if (! empty($searchIn)) {
                foreach ($searchIn as $file) {
                    $pageClass = Path::parseClass($addon->namespace(), $addon->realPath('src'), $file);
                    $implements = class_implements($pageClass, ResourcePage::class);
                    if (! isset($implements[ResourcePage::class])) {
                        continue;
                    }

                    $this->registerNav($pageClass);
                    Nucleo::pageMap([$pageClass]);
                }
            }
        });
    }

    protected function registerNav(string $section)
    {
        $implements = class_implements($section, HasSection::class);
        if (isset($implements[HasSection::class])) {
            $section = $section::getSection();

            $this->events->listen('navigation.'.$section['parent'].':building',
                function (SectionBag $bag) use ($section) {
                    if ($resolver = array_get($section, 'resolver')) {
                        $bag->add($resolver());
                    }
                });
        }
    }

    public function getClassName($file)
    {
        return str_replace('.php', '', basename($file));
    }
}