<?php

namespace Tests\Platform\Domains\Resource\Generator;

use SuperV\Platform\Domains\Resource\Generator\ResourceGenerator;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * @group excluded
 */
class ResourceGeneratorTest extends ResourceTestCase
{
    protected $tmpDirectory = 'resource-generator';

    function test_generates_resource_from_table()
    {
        $generator = ResourceGenerator::make();
        $generator->setTarget($this->tmpDirectory);

        $generator->withTableData('users', $this->getUsersTable());
        $this->assertFileEquals(
            __DIR__.'/fixtures/create_users_resource.php',
            $this->tmpDirectory.'/'.date('Y_m_d_His').'_create_users_resource.php'
        );

        $generator->withTableData('posts', $this->getPostsTable());
        $this->assertFileEquals(
            __DIR__.'/fixtures/create_posts_resource.php',
            $this->tmpDirectory.'/'.date('Y_m_d_His').'_create_posts_resource.php'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
//        Config::set('database.connections.testing', [
//            'driver'   => 'mysql',
//            'host'     => 'localhost',
//            'database' => 'sv_testing',
//            'username' => 'superv',
//            'password' => 'secret',
//        ]);

//        Config::set('database.default', 'sqlite');

//        Schema::disableForeignKeyConstraints();
//        Schema::dropIfExists('users');
//        Schema::dropIfExists('posts');
//        Schema::create('users', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//            $table->integer('age');
//        });
//        Schema::create('posts', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('title');
//            $table->unsignedInteger('user_id');
//
//            $table->foreign('user_id')->references('id')->on('users');
//        });
    }

    protected function tearDown(): void
    {
//        Schema::disableForeignKeyConstraints();
//        Schema::dropIfExists('users');
//        Schema::dropIfExists('posts');
//
//        Config::set('database.default', 'sqlite');
        parent::tearDown();
    }

    protected function getUsersTable()
    {
        return [
            'fields' =>
                [
                    'id'   =>
                        [
                            'field' => 'id',
                            'type'  => 'increments',
                        ],
                    'name' =>
                        [
                            'field' => 'name',
                            'type'  => 'string',
                        ],
                    'age'  =>
                        [
                            'field' => 'age',
                            'type'  => 'integer',
                        ],
                ],
            'keys'   =>
                [
                ],
        ];
    }

    protected function getPostsTable()
    {
        return [
            'fields' =>
                [
                    'id'          =>
                        [
                            'field' => 'id',
                            'type'  => 'increments',
                        ],
                    'title'       =>
                        [
                            'field' => 'title',
                            'type'  => 'string',
                        ],
                    'body'        =>
                        [
                            'field' => 'body',
                            'type'  => 'text',
                        ],
                    'subject'     =>
                        [
                            'field' => 'subject',
                            'type'  => 'string',
                        ],
                    'category_id' => [
                        'field'      => 'category_id',
                        'type'       => 'integer',
                        'decorators' =>
                            [
                                0 => 'unsigned',
                                1 => 'index',
                                2 => 'nullable',
                            ],
                    ],
                    'user_id'     =>
                        [
                            'field'      => 'user_id',
                            'type'       => 'integer',
                            'decorators' =>
                                [
                                    0 => 'unsigned',
                                    1 => 'index',
                                ],
                        ],
                ],
            'keys'   =>
                [
                    [
                        'name'       => null,
                        'field'      => 'user_id',
                        'references' => 'id',
                        'on'         => 'users',
                        'onUpdate'   => 'RESTRICT',
                        'onDelete'   => 'RESTRICT',
                    ],
                ],
        ];
    }
}
