<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use Platform;
use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Support\Parser;

class WriteDropletFiles
{
    /** @var \SuperV\Platform\Domains\Droplet\DropletModel */
    private $model;

    /** @var Filesystem */
    protected $filesystem;

    public function __construct(DropletModel $model)
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
            'droplet'     => [
                'class_name' => $dropletClass = "{$name}{$type}",
                'extends'    => ucwords($type),
                'short_name' => $shortName = $this->model->shortName(),
                'slug'       => $this->model->fullSlug(),
            ],
            'model'       => $this->model->toArray(),
            'psr4_prefix' => str_replace('\\', '\\\\', $this->model->namespace),
        ];

        $this->makeStub('droplets/'.strtolower($type).'.stub', $tokens, "src/{$dropletClass}.php");
        $this->makeStub('droplets/provider.stub', $tokens, "src/{$providerClass}.php");
        $this->makeStub('droplets/composer.stub', $tokens, 'composer.json');

        /**
         * test files
         */
        $this->makeStub('droplets/testing/phpunit.xml', [], 'phpunit.xml');
        $this->makeStub('droplets/testing/TestCase.stub', $tokens, "tests/{$shortName}/TestCase.php");
        $this->makeStub('droplets/testing/DropletTest.stub', $tokens, "tests/{$shortName}/{$shortName}Test.php");
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
