<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use Platform;
use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Support\Dispatchable;
use SuperV\Platform\Support\Parser;

class CreateAddonFiles
{
    use Dispatchable;

    /** @var Filesystem */
    protected $filesystem;

    /** @var \SuperV\Platform\Domains\Addon\AddonModel */
    private $model;

    public function __construct(AddonModel $model)
    {
        $this->model = $model;
    }

    public function handle(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $addonName = ucfirst(camel_case($this->model->getName()));
        $type = ucfirst(camel_case($this->model->type));

        $tokens = [
            'provider'    => [
                'class_name' => $providerClass = "{$addonName}{$type}ServiceProvider",
            ],
            'addon'       => [
                'vendor'        => $this->model->getVendor(),
                'name'          => $this->model->getName(),
                'type'          => $this->model->getType(),
                'class_name'    => $addonClass = "{$addonName}{$type}",
                'extends'       => ucwords($type),
                'domain'        => $addonName,
                'psr_namespace' => $this->model->getPsrNamespace(),
            ],
            'model'       => $this->model->toArray(),
            'psr4_prefix' => str_replace('\\', '\\\\', $this->model->getPsrNamespace()),
        ];

        $this->makeStub('addons/'.strtolower($type).'.stub', $tokens, "src/{$addonClass}.php");
        $this->makeStub('addons/provider.stub', $tokens, "src/{$providerClass}.php");
        $this->makeStub('addons/composer.stub', $tokens, 'composer.json');

        /**
         * test files
         */
        $this->makeStub('addons/testing/phpunit.xml', [], 'phpunit.xml');
        $this->makeStub('addons/testing/TestCase.stub', $tokens, "tests/{$addonName}/TestCase.php");
        $this->makeStub('addons/testing/AddonTest.stub', $tokens, "tests/{$addonName}/{$addonName}Test.php");
    }

    protected function makeStub($stub, $tokens, $target)
    {
        $stubbed = app(Parser::class)->parse($this->stubContent($stub), $tokens);

        $this->filesystem->put(base_path($this->model->path.'/'.$target), $stubbed);

        return $stubbed;
    }

    protected function stubContent($path)
    {
        return $this->filesystem->get($this->stubPath($path));
    }

    protected function stubPath($path)
    {
        return Platform::resourcePath("stubs/{$path}");
    }
}
