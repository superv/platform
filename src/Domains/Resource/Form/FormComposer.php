<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function payload()
    {
        $payload = new Payload([
            'url'     => $this->form->getUrl(),
            'method'  => $this->form->getMethod(),
            'fields'  => $this->composeFields(),
            'actions' => $this->composeActions(),
        ]);
        $this->form->fire('composed', ['form' => $this, 'payload' => $payload]);

        return $payload;
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
        return $this->form->getFields()
                          ->filter(function (Field $field) {
                              return ! $field->isHidden() && ! $field->hasFlag('form.hide');
                          })
                          ->map(function (Field $field) {
                              return array_filter(
                                  (new FieldComposer($field))->forForm($this->form->getEntry() ?? null)->get()
                              );
                          })->values()->all();
    }

    public function setRequest(?Request $request): FormComposer
    {
        $this->request = $request;

        return $this;
    }

    protected function getActions()
    {
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
                    'title'      => 'Save Changes',
                    'color'      => 'primary',
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
                'title'      => 'Create',
                'color'      => 'success',
            ],
        ];
    }

    public static function make(Form $form): FormComposer
    {
        return (new static($form));
    }
}