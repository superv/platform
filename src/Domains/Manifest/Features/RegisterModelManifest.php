<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\UI\Page\Features\RegisterManifestPages;
use SuperV\Platform\Domains\UI\Page\Jobs\BuildManifestPagesJob;

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

            $manifest->setModel($model);
        }

        $this->dispatch(new BuildManifestPagesJob($manifest));

        $this->serve(new RegisterManifestPages($manifest, $this->droplet));

        $manifests->push($manifest);

        return $manifest;
    }
}