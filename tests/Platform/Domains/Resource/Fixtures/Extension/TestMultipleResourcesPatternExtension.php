<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extension;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMultipleResources;

class TestMultipleResourcesPatternExtension implements ExtendsMultipleResources
{
    public function pattern()
    {
        return 'test_*';
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'extend')) {
            return $arguments[0]->setConfigValue('extended', true);
        }
    }
}