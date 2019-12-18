<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    public function payload()
    {
        $payload = new Payload([
            'identifier' => $this->form->getIdentifier(),
            'title'      => $this->form->getTitle(),
            'url'        => $this->form->getUrl(),
            'method'     => $this->form->getMethod(),
            'fields'     => $this->composeFields(),
            'actions'    => $this->composeActions(),
        ]);

        $this->form->fire('composed', ['form' => $this, 'payload' => $payload]);

        return $payload;
    }

    public function setRequest(?Request $request): FormComposer
    {
        $this->request = $request;

        return $this;
    }

    public static function make(FormInterface $form): FormComposer
    {
        return (new static($form));
    }

    protected function composeActions()
    {
        $actions = $this->getActions();

        if ($this->request && $this->request->get('action')) {
            $actions = collect($actions)->map(function ($action) {
                $action['default'] = $action['identifier'] === $this->request->get('action');

                return $action;
            })->all();
        }

        return $actions;
    }

    protected function composeFields()
    {
        return $this->form->fields()
                          ->visible()
                          ->sortBy(function (FormField $field) {
                              return $field->getRow();
                          })
                          ->map(function (FormField $field) {
                              return $field->compose();
                          })
                          ->values()
                          ->all();
    }

    protected function getActions()
    {
        if ($this->form->getActions()) {
            return $this->form->getActions();
        }

        if ($this->form->getEntry() && $this->form->getEntry()->exists) {
            return [
                [
                    'identifier' => 'view',
                    'title'      => '& View',
                    'color'      => 'light',
                ],
                [
                    'identifier' => 'edit_next',
                    'title'      => '& Edit Next',
                    'color'      => 'light',
                ],
                [
                    'default'    => true,
                    'identifier' => 'save',
                    'title'      => __('Save'),
                    'color'      => 'secondary',
                ],
            ];
        }

        return [
            [
                'identifier' => 'view',
                'title'      => '& View',
                'color'      => 'light',
            ],
            [
                'identifier' => 'create_another',
                'title'      => '& Another',
                'color'      => 'light',
            ],
            [
                'default'    => true,
                'identifier' => 'create',
                'title'      => __('Create'),
                'color'      => 'primary',
            ],
        ];
    }
}
