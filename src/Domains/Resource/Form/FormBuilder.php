<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Exceptions\PlatformException;

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

    protected $prebuilt = false;

    protected $skipFields = [];

    public function __construct(?Form $formy = null)
    {
        $this->form = $formy ?? new Form;
        $this->groups = collect();
    }

    public function prebuild(): self
    {
        if ($this->prebuilt) {
            PlatformException::fail('Form is already prebuilt');
        }

        $this->prebuilt = true;

        $this->groups->map(function (Group $group) {
            $group->setSkipFields($this->skipFields);
            $group->build();
        });

        $this->form->addGroups($this->groups);

        $this->form->cache();

        return $this;
    }

    public function addGroup(string $handle, Watcher $watcher = null, $fields): FormBuilder
    {
        $this->groups->push(new Group($handle, $watcher, $this->provideFields($fields)));

        return $this;
    }

    public function addFields($fields): FormBuilder
    {
        $this->groups->push(new NullWatcherGroup($this->provideFields($fields)));

        return $this;
    }

    public function removeField(string $name)
    {
        $this->skipFields[] = $name;

        return $this;
//        $this->fields = $this->fields->filter(function(Field $field) use ($name) {
//            return $field->getName() !== $name;
//        })->values();
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

    public function build(): self
    {
        $this->form->boot();

        return $this;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function makeFields()
    {
        $fields = $this->groups ?? $this->provider->provideFields();

        $fields = $fields->map(function ($field) {
            if ($field instanceof Field) {
                return $field;
            }

            return FieldFactory::createFromEntry($field);
        });

        return $fields;
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
        $form = Form::wakeup($uuid);

        return new static($form);
    }
}