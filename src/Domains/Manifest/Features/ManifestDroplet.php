<?php

namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Navigation\Section;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Navigation\Navigation;
use SuperV\Platform\Domains\Manifest\ManifestCollection;

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

    public function handle(ManifestCollection $manifests, Navigation $navigation)
    {
        $dropletManifests = $this->droplet->getManifests();
        if (! $dropletManifests || empty($dropletManifests) || ! $this->droplet->isNavigation()) {
            return;
        }
        $section = (new Section($navigation))->setTitle($this->droplet->getTitle())
                                             ->setIcon($this->droplet->getIcon())
                                             ->setModule($this->droplet)
                                             ->setSortOrder(10);

        /** @var ModelManifest $manifest */
        foreach ($dropletManifests as $manifest) {
            $manifest = $this->dispatch(new ManifestModel($manifest, $this->droplet));
            if ($pages = $manifest->pages()) {
                foreach ($pages as $page) {
                    if ($page->isNavigation()) {
                        $section->addPage($page);
                    }
                }
            }
        }
        $manifests = $manifests->merge($dropletManifests);

        $navigation->addSection($section);
    }
}
