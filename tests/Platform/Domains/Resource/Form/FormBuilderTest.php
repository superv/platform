<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Parser;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormBuilderTest extends ResourceTestCase
{
    /** @test */
    function builds_create_form()
    {
        $form = (new FormBuilder)
            ->addFields($fields = $this->makeFields())
            ->prebuild()
            ->getForm();

        $this->assertNotNull($form->uuid());
        $this->assertNotNull(FormBuilder::wakeup($form->uuid()));
        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose(),
                $form->getField('age')->compose(),
            ],
        ], $form->compose());
    }

    /** @test */
    function builds_update_form()
    {
        $fields = $this->makeFields();

        $form = (new FormBuilder)
            ->addGroup('test_user', new TestUser(['name' => 'Omar', 'age' => 33]), $fields)
            ->prebuild()
            ->getForm();

        $this->assertEquals('Omar', $form->getField('name')->compose()['value']);
        $this->assertEquals(33, $form->getField('age')->compose()['value']);
    }

    /** @test */
    function saves_form()
    {
        $builder = (new FormBuilder)
            ->addFields($fields = $this->makeFields())
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->value()->get());
        $this->assertEquals(33, $form->getField('age')->value()->get());
    }

    /** @test */
    function entry_is_saved_with_form()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, $this->makeFields())
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->value()->get());
        $this->assertEquals(33, $form->getField('age')->value()->get());

        $user = $form->getWatcher('test_user');
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }

    /** @test */
    function where_are_the_field_types()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, ResourceModel::withSlug('test_users'))
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm();

        $form->save();

        $user = $form->getWatcher('test_user');

        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }

    /**
     * @return array
     */
    public function makeFields(): array
    {
        return [
            $name = Field::make(['name' => 'name', 'type' => 'text']),
            $age = Field::make(['name' => 'age', 'type' => 'number']),
        ];
    }
}

class TestUser extends Model implements Watcher
{
    public $timestamps = false;

    protected $guarded = [];

    public function setAttribute($key, $value)
    {
        return parent::setAttribute($key, $value);
    }

    public function save(array $options = [])
    {
        return parent::save($options);
    }
}