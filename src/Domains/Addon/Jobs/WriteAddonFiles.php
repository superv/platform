<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use Platform;
use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Support\Parser;

class WriteAddonFiles
{
    /** @var \SuperV\Platform\Domains\Addon\AddonModel */
    private $model;

    /** @var Filesystem */
    protected $filesystem;

    public function __construct(AddonModel $model)
    {
        $this->model = $model;
    }

    public function handle(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $name = ucfirst(camel_case($this->model->name));
        $type = ucfirst(camel_case($this->model->type));

        $tokens = [
            'provider'    => [
                'class_name' => $providerClass = "{$name}{$type}ServiceProvider",
            ],
            'addon'       => [
                'class_name' => $addonClass = "{$name}{$type}",
                'extends'    => ucwords($type),
                'short_name' => $shortName = $this->model->shortName(),
                'slug'       => $this->model->fullSlug(),
            ],
            'model'       => $this->model->toArray(),
            'psr4_prefix' => str_replace('\\', '\\\\', $this->model->namespace),
        ];

        $this->makeStub('addons/'.strtolower($type).'.stub', $tokens, "src/{$addonClass}.php");
        $this->makeStub('addons/provider.stub', $tokens, "src/{$providerClass}.php");
        $this->makeStub('addons/composer.stub', $tokens, 'composer.json');

        /**
         * test files
         */
        $this->makeStub('addons/testing/phpunit.xml', [], 'phpunit.xml');
        $this->makeStub('addons/testing/TestCase.stub', $tokens, "tests/{$shortName}/TestCase.php");
        $this->makeStub('addons/testing/AddonTest.stub', $tokens, "tests/{$shortName}/{$shortName}Test.php");
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
