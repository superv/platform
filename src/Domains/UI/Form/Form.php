<?php namespace SuperV\Platform\Domains\UI\Form;

use SuperV\Platform\Support\Collection;
use Symfony\Component\Form\FormInterface;

class Form
{
    protected $fields;

    protected $actions;

    /** @var  FormInterface */
    protected $symfonyForm;

    public function __construct(Collection $fields, Collection $actions)
    {
        $this->fields = $fields;
        $this->actions = $actions;
    }

    public function addField(FieldType $field)
    {
        $this->fields->push($field);
    }

    public function addAction(Action $action)
    {
        $this->actions->push($action);
    }

    public function isSubmitted()
    {
        return $this->symfonyForm->isSubmitted();
    }

    public function handleRequest()
    {
        return $this->symfonyForm->handleRequest();
    }

    public function isValid()
    {
        return $this->symfonyForm->isValid();
    }

    public function createView()
    {
        return $this->symfonyForm->createView();
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    /**
     * @return Collection
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @return mixed
     */
    public function getSymfonyForm()
    {
        return $this->symfonyForm;
    }

    /**
     * @param FormInterface $symfonyForm
     */
    public function setSymfonyForm(FormInterface $symfonyForm)
    {
        $this->symfonyForm = $symfonyForm;
    }

    public function getFormData($key)
    {
        return $this->symfonyForm->get($key)->getData();
    }
}