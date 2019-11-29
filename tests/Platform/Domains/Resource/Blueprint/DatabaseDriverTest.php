<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Field\Types\TextField;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DatabaseDriverTest extends ResourceTestCase
{
    function test__run()
    {
        $blueprint = Builder::resource('core.posts', function (Blueprint $resource) {
            $resource->addField('title', TextField::class);
        });

        $blueprint->getDriver()->run($blueprint);

        $this->assertTableExists('posts');
        $this->assertColumnExists('posts', 'id');
        $this->assertColumnExists('posts', 'title');

        $indexes = SchemaService::resolve()->getIndexes('posts');
    }
}