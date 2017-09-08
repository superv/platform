<?php

namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\ManifestBuilder;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Navigation\Navigation;

class ManifestDroplet extends Feature
{
    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle(ManifestCollection $manifests, Navigation $navigation, ManifestBuilder $builder)
    {
        $dropletManifests = $this->droplet->getManifests();
        if (! $dropletManifests || empty($dropletManifests) || ! $this->droplet->isNavigation()) {
            return;
        }

        $section = $navigation->addDropletSection($this->droplet);

        /** @var ModelManifest $manifest */
        foreach ($dropletManifests as $dataModel) {
            $manifest = $builder->reset()
                                ->setDataModel($dataModel)
                                ->setDroplet($this->droplet)
                                ->build()
                                ->getManifest();

//            $manifest = $this->dispatch(new ManifestModel($manifest, $this->droplet));
            if ($pages = $manifest->getPages()) {
                foreach ($pages as $page) {
                    if ($page->isNavigation()) {
                        $section->addPage($page);
                    }
                }
            }
        }
//        $manifests = $manifests->merge($dropletManifests);
    }
}
