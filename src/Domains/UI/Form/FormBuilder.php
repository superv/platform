<?php namespace SuperV\Platform\Domains\UI\Form;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\Form\Features\BuildForm;
use SuperV\Platform\Domains\UI\Form\Features\MakeForm;
use Symfony\Component\Form\FormInterface;

class FormBuilder
{
    use ServesFeaturesTrait;

    /** @var  EntryModel */
    protected $entry;

    /** @var  Form */
    protected $form;

    /** @var  FormInterface */
    protected $factory;

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

    public function make($entry)
    {
        $this->entry = $entry;

        $this->build();

        $this->serve(new MakeForm($this));

        return $this->post();
    }

    public function post()
    {
        if (app('request')->isMethod('post')) {

            $this->form->handleRequest();

            if ($this->form->isSubmitted() && $this->form->isValid()) {

//                if ($relationships = $this->entry->getRelationships()) {
//                    foreach ($relationships as $relationship) {
//                        if ($formValue = $this->form->getFormData($relationship)) {
//                            $relation = $this->entry->{$relationship}();
//                            if ($relation instanceof HasOne) {
//                                $this->entry->setAttribute("{$relationship}_id", $formValue);
//                            } elseif ($relation instanceof BelongsToMany) {
//                                $this->entry->{$relationship}()->sync($formValue);
//                            }
//                        }
//                    }
//                }

                $this->entry->save();

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

        return $this->view->make('superv::form.form', ['form' => $response]);
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
}