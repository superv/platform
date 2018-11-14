<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Support\Dispatchable;

class PostForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /** @var array */
    protected $callbacks;

    public function __construct(Form $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function handle()
    {
        return null;
        $all = $this->request->all();
        $this->form->getFields()->map(function (FieldType $field) {
            $entry = $field->getEntry()->toArray();

            $this->callbacks[] = $field->setValueFromRequest($this->request);

            $entry = $field->getEntry()->toArray();
            $value = $field->getValue();

            $entry2 = $field;
        });

        $this->form->getFields()->map(function (FieldType $field) {
            $entry = $field->getEntry();

            if ($entry->exists && ! $entry->isDirty()) {
                return;
            }

            $entry->save();
        });

//        $this->form->getResources()->map(function (Resource $resource) {
//            $resource->saveEntry(['form' => $this->form]);
//        });

        collect($this->callbacks)->filter()->map(function (\Closure $callback) {
            $callback();
        });
    }
}