<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\DropletManifest;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Menu\Menu;
use SuperV\Platform\Domains\UI\Menu\Section;

class RegisterDropletManifest extends Feature
{
    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(DropletManifest $manifest, Droplet $droplet)
    {
        $this->manifest = $manifest;
        $this->droplet = $droplet;
    }

    public function handle(ManifestCollection $manifests, Menu $menu, Section $section)
    {
        $section->setTitle(title_case($this->droplet->getName()))
                ->setSortOrder(10);

        if ($modelManifests = $this->manifest->getManifests()) {
            /** @var ModelManifest $manifest */
            foreach ($modelManifests as $manifest) {
                $manifest = $this->dispatch(new RegisterModelManifest($manifest, $this->droplet));
                if ($pages = $manifest->pages()) {
                    foreach ($pages as $page) {
                        if ($page->isNavigation()) {
                            $section->addPage($page);
                        }
                    }
                }
            }
        }
        $manifests->push($this->manifest);

        $menu->addSection($section);
    }
}