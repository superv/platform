<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Field\Watcher;

class FormBuilder
{
    /**
     * @var ProvidesFields
     */
    protected $provider;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $groups;

    protected $skipFields = [];

    public function __construct(?Form $formy = null)
    {
        $this->form = $formy ?? new Form;
        $this->groups = collect();
    }

    public function sleep(): self
    {
        $this->form->sleep();

        return $this;
    }

    public function addGroup(string $handle, Watcher $watcher = null, $fields): FormBuilder
    {
        $fields = $this->provideFields($fields);

        $this->form->mergeFields($fields, $watcher, $handle);

        return $this;
    }

    public function addFields($fields): FormBuilder
    {
        $this->form->mergeFields(collect($fields), null, 'default');

        return $this;
    }

    public function removeField(string $name)
    {
        $this->skipFields[] = $name;

        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->form->setRequest($request);

        return $this;
    }

    public function setProvider(ProvidesFields $provider): FormBuilder
    {
        $this->provider = $provider;

        return $this;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    protected function provideFields($fields)
    {
        if ($fields instanceof ProvidesFields) {
            $fields = $fields->provideFields();
        }

        if (is_array($fields)) {
            $fields = collect($fields);
        }

        return $fields;
    }

    public function uuid()
    {
        return $this->form->uuid();
    }

    public static function wakeup($uuid): self
    {
        $form = Form::fromCache($uuid);

        $form->wakeup();

        return new static($form);
    }
}