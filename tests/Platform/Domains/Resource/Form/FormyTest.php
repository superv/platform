<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldValue;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\Formy;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormyTest extends ResourceTestCase
{
    /** @test */
    function builds_form_with_minimal_data()
    {
        $fields = [
            $name = new Field('name', 'text'),
            $age = new Field('age', 'number'),
        ];

        $form = (new Formy($fields))->boot();
        $this->assertNotNull(Formy::wakeup($form->uuid()));

        $this->assertNotNull($form->uuid());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $name->compose(),
                $age->compose(),
            ],
        ], $form->compose());
    }

    /** @test */
    function saves_form()
    {
        $fields = [
            $name = new Field('name', 'text'),
            $age = new Field('age', 'number'),
        ];

        $form = (new Formy($fields))->boot();

        $form->request(Request::create('', 'POST', ['name' => 'Omar', 'age' => 33]));

        $form->save();

        $this->assertEquals('Omar', $name->value()->get());
        $this->assertEquals(33, $age->value()->get());
    }

    /** @test */
    function entry_is_saved_with_form()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $fields = [
            $name = new Field('name', 'text'),
            $age = new Field('age', 'number'),
        ];

        $user = new TestUser();
        $name->addWatcher($user);
        $age->addWatcher($user);

        $form = (new Formy($fields))->boot();
        $form->addWatcher($user);

        $form->request(Request::create('', 'POST', ['name' => 'Omar', 'age' => 33]));
        $form->save();

        $this->assertEquals('Omar', $name->value()->get());
        $this->assertEquals(33, $age->value()->get());

        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }
}

class TestUser extends Model implements Watcher
{
    protected $guarded = [];

    public $timestamps = false;

    public function watchableUpdated($params)
    {
        if ($params instanceof FieldValue) {
            $this->setAttribute($params->fieldName(), $params->get());
        }

        if ($params instanceof Formy) {
            $this->save();
        }
    }
}