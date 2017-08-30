<?php

namespace SuperV\Platform\Domains\UI\Form;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\Form\Features\BuildForm;
use SuperV\Platform\Domains\UI\Form\Features\MakeForm;
use SuperV\Platform\Domains\UI\Form\Jobs\MakeFormButtons;
use SuperV\Platform\Traits\FiresCallbacks;
use Symfony\Component\Form\FormInterface;

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

    public function make($entry)
    {
        $this->entry = $entry;

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

                return redirect()->back()->withSuccess('Entry saved!');
            }
        }

        return $this;
    }

    public function render($entry)
    {
        $response = $this->make($entry);
        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $response = $this->form->createView();

        $view = $this->ajax ? 'superv::form.ajax' : 'superv::form.form';

        return $this->view->make($view, ['response' => $response, 'form' => $this->getForm()]);
    }

    /**
     * @return EntryModel
     */
    public function getEntry(): EntryModel
    {
        return $this->entry;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param bool $ajax
     *
     * @return FormBuilder
     */
    public function setAjax(bool $ajax): FormBuilder
    {
        $this->ajax = $ajax;

        return $this;
    }

    public function onSaving(EntryModel $entry)
    {
    }

    public function onSaved(EntryModel $entry)
    {
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }
}
