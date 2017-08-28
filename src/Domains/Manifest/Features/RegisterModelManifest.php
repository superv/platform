<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Page\Features\MakePages;
use SuperV\Platform\Domains\UI\Page\Jobs\BuildManifestPagesJob;
use SuperV\Platform\Domains\UI\Page\Page;

class RegisterModelManifest extends Feature
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
        $manifest = is_object($this->manifest) ? $this->manifest : superv($this->manifest);

        $model = $manifest->getModel();

        if ($model === null) {
            $parts = explode('\\', str_replace('Manifest', 'Model', get_class($manifest)));

            $model = implode('\\', $parts);

            if (class_exists($model)) {
                $manifest->setModel($model);
            }
        }

        \Debugbar::startMeasure('BuildManifestPagesJob');
        $this->dispatch(new BuildManifestPagesJob($manifest));
        \Debugbar::stopMeasure('BuildManifestPagesJob');

        if ($model = $manifest->getModel()) {
            $manifest->pages()->map(function (Page $page) use ($manifest) {
                $page->setModel($manifest->getModel())
                     ->setManifest($manifest);
            });
        }

        \Debugbar::startMeasure('MakePages');
        $this->serve(new MakePages($manifest->pages(), $this->droplet));
        \Debugbar::stopMeasure('MakePages');

        $manifests->push($manifest);

        return $manifest;
    }
}