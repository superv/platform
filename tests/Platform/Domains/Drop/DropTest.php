<?php

namespace Tests\Platform\Domains\Drop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Drop\BaseRepoHandler;
use SuperV\Platform\Domains\Drop\Contracts\Drop as DropContract;
use SuperV\Platform\Domains\Drop\DropRepoModel;
use SuperV\Platform\Domains\Drop\Drops;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Testing\PlatformTestCase;

class DropTest extends PlatformTestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    function test__create()
    {
        $blogRepo = $this->makeDropRepo('blog', 'posts', BaseRepoHandler::class);
        $drop = $blogRepo->createDrop(['key' => 'title']);

        $this->assertEquals(1, sv_resource('sv_drops')->count());
        $this->assertInstanceOf(DropContract::class, $drop);
        $this->assertEquals('posts', $drop->getRepoIdentifier());
        $this->assertEquals(BaseRepoHandler::class, $drop->getRepoHandler());
        $this->assertEquals('title', $drop->getDropKey());
    }

    function test__resolve()
    {
        $blogPostsRepo = $this->makeDropRepo('blog', 'posts', BaseRepoHandler::class);
        $blogPostsRepo->createDrop(['key' => 'title']);
        $blogPostsRepo->createDrop(['key' => 'content']);
        $posts = $this->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
        });
        $post = $posts->create(['id' => 321, 'title' => 'My Post', 'content' => 'Post Content']);

        $blogCommentsRepo = $this->makeDropRepo('blog', 'comments', BaseRepoHandler::class);
        $blogCommentsRepo->createDrop(['key' => 'comment']);
        $comments = $this->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('comment');
        });
        $comment = $comments->create(['id' => 456, 'comment' => 'My Comment']);

        $drops = Drops::make([
            'blog.posts'    => $post,
            'blog.comments' => $comment,
        ])
                      ->resolve([
                          'blog.posts::title',
                          'blog.posts::content',
                          'blog.comments::comment',
                      ]);

        $title = $drops->get('blog.posts::title');
        $this->assertEquals('My Post', $title->getEntryValue());

        $content = $drops->get('blog.posts::content');
        $this->assertEquals('Post Content', $content->getEntryValue());

        $comment = $drops->get('blog.comments::comment');
        $this->assertEquals('My Comment', $comment->getEntryValue());
    }


    protected function makeDropRepo(string $namespace, string $identifier, string $handler): DropRepoModel
    {
        return
            $repo = sv_resource('sv_drop_repos')->create([
                'namespace'  => $namespace,
                'identifier' => $identifier,
                'handler'    => $handler,
            ]);
    }
}
