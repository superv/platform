<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use Hub;
use Platform;
use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Skeleton;
use SuperV\Platform\Support\Dispatchable;
use SuperV\Platform\Support\Parser;

class CreateAddonFiles
{
    use Dispatchable;

    /** @var Filesystem */
    protected $filesystem;

    /** @var \SuperV\Platform\Domains\Addon\AddonModel */
    private $addon;

    protected $skeletons = [
        'panel' => 'skeletons/panel',
    ];

    public function __construct(AddonModel $addon)
    {
        $this->addon = $addon;
    }

    public function handle(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $addonLabel = ucfirst(camel_case($this->addon->getName()));
        $typeLabel = ucfirst(camel_case($this->addon->getType()));

        $tokens = [
            'provider'    => [
                'class_name' => $providerClass = "{$addonLabel}{$typeLabel}ServiceProvider",
            ],
            'addon'       => array_merge($this->addon->toArray(), [
                'class_name' => $addonClass = "{$addonLabel}{$typeLabel}",
                'extends'    => ucwords($typeLabel),
                'domain'     => $addonLabel,
            ]),
            'psr4_prefix' => str_replace('\\', '\\\\', $this->addon->getPsrNamespace()),
        ];

        $this->makeStub('addons/'.strtolower($typeLabel).'.stub', $tokens, "src/{$addonClass}.php");
        $this->makeStub('addons/provider.stub', $tokens, "src/{$providerClass}.php");
        $this->makeStub('addons/composer.stub', $tokens, 'composer.json');

        /**
         * test files
         */
        $this->makeStub('addons/testing/phpunit.xml.dist', [], 'phpunit.xml.dist');
        $this->makeStub('addons/testing/TestCase.stub', $tokens, "tests/{$addonLabel}/TestCase.php");
        $this->makeStub('addons/testing/AddonTest.stub', $tokens, "tests/{$addonLabel}/{$addonLabel}Test.php");

        if ($skeletonPath = array_get($this->skeletons, $this->addon->getType())) {
            $portSlug = 'api';
            $port = Hub::get($portSlug);

            $contentTokens = array_merge($tokens, [
                'panel'    => [
                    'title'           => "{$addonLabel}{$typeLabel}",
                    'label'           => $addonLabel,
                    'name'            => $this->addon->getName(),
                    'base_path'       => $this->addon->getName(),
                    'port'            => $port->slug(),
                    'dev_server_host' => $port->hostname(),
                    'dev_server_port' => '8091',
                ],
                'api'      => [
                    'scheme'    => $port->scheme(),
                    'host'      => $port->hostname(),
                    'base_path' => $port->baseUrl(),
                ],
                'platform' => [
                    'version' => \SuperV\Platform\Platform::$version,
                ],
            ]);

            $fileTokens = [
                'Panel' => "{$addonLabel}{$typeLabel}",
            ];

            Skeleton::resolve()
                    ->from(Platform::resourcePath($skeletonPath))
                    ->withTokens($contentTokens, $fileTokens)
                    ->copyTo($this->getTargetPath());
        }
    }

    protected function makeStub($stub, $tokens, $target)
    {
        $stubbed = app(Parser::class)->parse($this->stubContent($stub), $tokens);

        $this->filesystem->put($this->getTargetPath($target), $stubbed);

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

    protected function getTargetPath($target = null): string
    {
        return base_path($this->addon->path.($target ? '/'.$target : ''));
    }
}
