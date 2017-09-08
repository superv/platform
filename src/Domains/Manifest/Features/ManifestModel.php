<?php

namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Jobs\BuildManifestPages;
use SuperV\Platform\Domains\Manifest\Jobs\SetManifestModel;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Page\Features\MakePages;
use SuperV\Platform\Domains\UI\Page\Page;

class ManifestModel extends Feature
{
    /**
     * @var ModelManifest|string
     */
    private $manifest;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct($manifest, Droplet $droplet)
    {
        $this->manifest = $manifest;
        $this->droplet = $droplet;
    }

    public function handle(ManifestCollection $manifests)
    {
        $manifest = is_object($this->manifest) ? $this->manifest : app($this->manifest);

        $this->dispatch(new SetManifestModel($manifest));

        $this->dispatch(new BuildManifestPages($manifest));

        if ($model = $manifest->getModel()) {
            $manifest->pages()->map(function (Page $page) use ($manifest) {
                $page->setModel($manifest->getModel())
                     ->setManifest($manifest)
                     ->setPort($manifest->getPort());
            });
        }

        \Debugbar::startMeasure('MakePages');
        $this->serve(new MakePages($manifest->pages(), $this->droplet));
        \Debugbar::stopMeasure('MakePages');

        $manifests->push($manifest);

        return $manifest;
    }
}
