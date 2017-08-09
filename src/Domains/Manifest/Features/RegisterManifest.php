<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\UI\Page\Features\RegisterPage;
use SuperV\Platform\Domains\UI\Page\Jobs\BuildPageJob;
use SuperV\Platform\Domains\UI\Page\Page;

class RegisterManifest extends Feature
{
    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Manifest $manifest, Droplet $droplet)
    {
        $this->manifest = $manifest;
        $this->droplet = $droplet;
    }

    public function handle(ManifestCollection $manifests)
    {
        $model = $this->manifest->getModel();

        if ($model === null) {
            $parts = explode('\\', str_replace('Manifest', 'Model', get_class($this->manifest)));

            $model = implode('\\', $parts);

            $this->manifest->setModel($model);
        }

        foreach ($this->manifest->getPages() as $page => $pageData) {
            array_set($pageData, 'page', $page);

            /** @var Page $page */
            $page = $this->dispatch(new BuildPageJob($this->manifest, $pageData));

            $page->setDroplet($this->droplet);

            $this->dispatch(new RegisterPage($page));
        }

        $manifests->push($this->manifest);
    }
}