<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\Field\Field;

class PostsFields
{
    public static $identifier = 'sv.testing.posts.fields';

    public function resolvedTitle(Field $title)
    {
        $_SERVER['__hooks::fields.title.resolved'] = $title->getIdentifier();
    }
}
