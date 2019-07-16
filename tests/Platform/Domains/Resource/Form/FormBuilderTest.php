<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormBuilderTest
 *
 * @package Tests\Platform\Domains\Resource\Form
 * @group   resource
 */
class FormBuilderTest
{
    function  __build()
    {
       $posts = $this->schema()->posts();

        $builder = new FormBuilder();
        $builder->setResource($posts);

        $form = $builder->build();
        $this->assertNotEquals(0, $form->getFields()->count());
//        dd($form->getFields());
    }
}
