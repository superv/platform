<?php

namespace Tests\Platform\Domains\Resource\Field\Features;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DynamicFieldConfiguration extends ResourceTestCase
{
    function test__()
    {
        $resource = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone');
        });

        $phone = $resource->getField('phone');
        $phone->removeRules();

        $this->addToAssertionCount(1);
    }
}
