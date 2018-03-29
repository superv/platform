<?php

namespace SuperV\Platform\Domains\Asset;

use Twig_SimpleFunction;

class Extension extends \Twig_Extension
{
    /**
     * @var \SuperV\Platform\Domains\Asset\Asset
     */
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'app_env',
                function ($environment = null) {
                    if (!$environment) {
                        return app()->environment();
                    }

                    return app()->environment() === $environment;
                }
            ),
            new Twig_SimpleFunction(
                'asset_*',
                function ($name) {
                    $arguments = array_slice(func_get_args(), 1);

                    return call_user_func_array([$this->asset, camel_case($name)], $arguments);
                }, ['is_safe' => ['html']]
            ),
        ];
    }
}
