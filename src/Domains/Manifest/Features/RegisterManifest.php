<?php namespace SuperV\Platform\Domains\Manifest\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Manifest\Manifest;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\UI\Page\Features\RegisterPage;
use SuperV\Platform\Domains\UI\Page\Jobs\BuildPageJob;

class RegisterManifest extends Feature
{
    /**
     * @var Manifest
     */
    private $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }

    public function handle(ManifestCollection $manifests)
    {
        $model = $this->manifest->getModel();

        if ($model === null) {
            $parts = explode('\\', str_replace('Manifest', 'Model', get_class($this->manifest)));

//            unset($parts[count($parts) - 2]);

            $model = implode('\\', $parts);

            $this->manifest->setModel($model);
        }

        foreach ($this->manifest->getPages() as $page => $pageData) {
            array_set($pageData, 'page', $page);
            $page = $this->dispatch(new BuildPageJob($this->manifest, $pageData));

            $this->dispatch(new RegisterPage($page));
        }

        $manifests->push($this->manifest);
    }
}