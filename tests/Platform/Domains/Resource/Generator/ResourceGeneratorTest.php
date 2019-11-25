<?php

namespace Tests\Platform\Domains\Resource\Generator;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use SuperV\Platform\Domains\Auth\Profile;
use SuperV\Platform\Domains\Resource\Generator\RelationGenerator;
use SuperV\Platform\Domains\Resource\Generator\ResourceGenerator;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * @group excluded
 */
class ResourceGeneratorTest extends ResourceTestCase
{
    protected $tmpDirectory = 'resource-generator';

    function __generates_resource_from_table()
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

    function test__model_test()
    {
        $path = __DIR__;

        $classList = [];
        /** @var SplFileInfo $file */
        foreach ((new Finder)->in($path)->files() as $file) {
            if (! $namespace = get_ns_from_file($file->getPathname())) {
                continue;
            }

            $className = str_replace('.php', '', $file->getFilename());
            $class = $namespace.'\\'.$className;

            $classList[] = $class;
        }

        $models = collect($classList)
            ->filter(function ($class) {
                if (! class_exists($class)) {
                    return false;
                }

                $reflection = new ReflectionClass($class);
                if (! $reflection->isUserDefined()) {
                    return false;
                }

                if ($reflection->isAbstract()) {
                    return false;
                }

                return $reflection->isSubclassOf(Model::class);
            })
            ->keyBy(function ($model) {
                return (new $model)->getTable();
            });

        $this->assertEquals(ImportUserModel::class, $models->get('imp_users'));
        $this->assertEquals(ImportPostModel::class, $models->get('imp_posts'));
    }

    function test__relation_test()
    {
        $generator = new RelationGenerator(ImportUserModel::class);

        $relations = $generator->make();

        $this->assertEquals(2, $relations->count());

        /** @var RelationConfig $posts */
        $posts = $relations->get('posts');
        $this->assertInstanceOf(RelationConfig::class, $posts);
        $this->assertEquals([
            'related_model' => ImportPostModel::class,
            'foreign_key'   => 'user_id',
            'local_key'     => 'id',
        ], $posts->toArray());

        /** @var RelationConfig $posts */
        $profile = $relations->get('profile');
        $this->assertInstanceOf(RelationConfig::class, $profile);
        $this->assertEquals([
            'related_model' => Profile::class,
            'foreign_key'   => 'user_id',
        ], $profile->toArray());
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
