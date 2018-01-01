<?php

namespace SuperV\Platform\Domains\UI\DeprecatedForm;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\DeprecatedForm\Features\BuildForm;
use SuperV\Platform\Domains\UI\DeprecatedForm\Features\MakeForm;
use SuperV\Platform\Domains\UI\DeprecatedForm\Jobs\MakeFormButtons;
use SuperV\Platform\Traits\FiresCallbacks;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class FormBuilder
{
    use ServesFeaturesTrait;
    use FiresCallbacks;

    protected $ajax = false;

    /** @var EntryModel */
    protected $entry;

    /** @var Form */
    protected $form;

    /** @var FormInterface */
    protected $factory;

    protected $skips = [];

    protected $buttons = [];

    /**
     * @var Factory
     */
    private $view;

    private $formAttributes = [];

    public function __construct(Form $form, Factory $view)
    {
        $this->form = $form;
        $this->view = $view;
    }

    public function build()
    {
        $this->serve(new BuildForm($this));
    }

    public function skipFields($fields)
    {
        $this->skips = array_filter(array_merge($this->skips, $fields));

        return $this;
    }

    public function hasSkip($field)
    {
        return in_array($field, $this->skips);
    }

    public function make()
    {
        $this->build();

        $this->serve(new MakeForm($this));

        $this->serve(new MakeFormButtons($this));

        return $this->post();
    }

    public function post()
    {
        if (app('request')->isMethod('post')) {
            $this->form->handleRequest();

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->fire('saving', ['entry' => $this->entry]);
                $this->entry->save();
                $this->fire('saved', ['entry' => $this->entry]);

                if (!request()->wantsJson()) {
                    return redirect()->back()->withSuccess('Entry saved!');
                } else {
                    return response()->json(['status' => 'ok']);
                }
            }
        }

        return $this;
    }

    public function render($entry = null)
    {
        if ($entry) {
            $this->setEntry($entry);
        }

        $response = $this->make();
        if ($response instanceof Response) {
            return $response;
        }

        $response = $this->form->createView();

        $wrapper = $this->ajax ? 'superv::form.ajax' : 'superv::form.form';

        return $this->view->make($wrapper, ['response' => $response, 'form' => $this->getForm()]);
    }

    /**
     * @return EntryModel
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param EntryModel $entry
     *
     * @return FormBuilder
     */
    public function setEntry(EntryModel $entry): FormBuilder
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    public function getPostData($key, $default = null)
    {
        return $this->getForm()->getFormData($key, $default);
    }

    /**
     * @param bool $ajax
     *
     * @return FormBuilder
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function isCreating()
    {
        return $this->isFormMode('create');
    }

    public function isEditing()
    {
        return $this->isFormMode('edit');
    }

    public function isFormMode($mode)
    {
        return $this->getForm()->getMode() == $mode;
    }

    public function getFormAttributes()
    {

        $attributes = $this->formAttributes;

        if ($this->ajax) {
            array_set($attributes, 'class', 'ajax');
        }

        return $attributes;
    }
}
