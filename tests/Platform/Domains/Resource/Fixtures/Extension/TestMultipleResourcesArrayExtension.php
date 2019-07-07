<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extension;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMultipleResources;

class TestMultipleResourcesArrayExtension implements ExtendsMultipleResources
{
    public function pattern()
    {
        return ['test_users', 'test_posts'];
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'extend')) {
            return $arguments[0]->setConfigValue('extended', true);
        }
    }
}