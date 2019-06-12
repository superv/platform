<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FormField;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form as FormContract;
use SuperV\Platform\Domains\Resource\Form\Jobs\ValidateForm;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form implements FormContract, ProvidesUIComponent
{
    use FiresCallbacks;
    const MODE_CREATE = 'create';
    const MODE_UPDATE = 'update';

    /** @var string */
    protected $identifier;

    /** @var Resource */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field[]|Collection
     */
    protected $fields;

    protected $hiddenFields = [];

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /** @var EntryContract */
    protected $entry;

    protected $actions = [];

    protected $postSaveCallbacks = [];

    protected $title;

    protected $mode = Form::MODE_CREATE;

    public function make($uuid = null)
    {
        $this->uuid = $uuid ?? uuid();

        if (is_null($this->fields)) {
            if ($this->entry) {
                $this->fields = $this->provideFields($this->entry);
            } else {
                $this->fields = collect();
            }
        }

        $this->fields->map(function (Field $field) {
            $field->setForm($this);

            if ($this->hasEntry()) {
                $field->setWatcher($this->getEntry());
            }

            if (in_array($field->getColumnName(), $this->getHiddenFields())) {
                $field->hide();
            }

            if ($this->hasEntry()) {
                $field->fillFromEntry($this->getEntry());
            }
        });

        return $this;
    }

    public function save(): FormResponse
    {
        // If this is a no-entry form then we will
        // have to validate on our own since the
        // model events wont fire
        //
        if (! $this->hasEntry()) {
            $this->validate();
        }

        if ($this->hasEntry() && $this->entry->exists) {
            $this->mode = Form::MODE_UPDATE;
        }

        $this->fields->map(function (Field $field) {
            if ($field->isHidden()) {
                return;
            }

            $this->postSaveCallbacks[] = $field->resolveRequest($this->request, $this->getEntry());
        });

        if ($this->hasEntry()) {
            $this->getEntry()->save();
        }

        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });

        return new FormResponse($this, $this->resource, $this->getEntry());
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function isUpdating()
    {
        return $this->mode === Form::MODE_UPDATE;
    }

    public function validate()
    {
        ValidateForm::dispatch($this, $this->request->all());
    }

    public function compose(): Payload
    {
        return FormComposer::make($this)->setRequest($this->request)->payload();
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-form')
                        ->setProps(
                            $this->compose()->get()
                        );
    }

    public function mergeFields($fields)
    {
        $fields = $this->provideFields($fields);
        $this->fields = $this->fields->merge($fields);

        return $this;
    }

    public function hideField(string $fieldName): self
    {
        if (! $field = $this->getField($fieldName)) {
            throw new Exception('Field not found: '.$fieldName);
        }

        $field->hide();

        $this->hiddenFields[] = $fieldName;

        return $this;
    }

    public function hideFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFields()->map(function (Field $field) use ($fields) {
            if (in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function onlyFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFields()->map(function (Field $field) use ($fields) {
            if (! in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function addField($field)
    {
        return $this->addFields([$field]);
    }

    public function addFields($fields): self
    {
        return $this->mergeFields($fields);
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $this->provideFields($fields);

        return $this;
    }

    public function getField(string $name): ?Field
    {
        return $this->fields->first(
            function (Field $field) use ($name) {
                return $field->getName() === $name;
            });
    }

    public function getFieldValue(string $name)
    {
        return $this->getField($name)->getValue();
    }

    public function composeField($field, $entry = null)
    {
        if (is_string($field)) {
            $field = $this->getField($field);
        }

        return (new FieldComposer($field))->forForm($entry);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url): Form
    {
        $this->url = $url;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setResource(Resource $resource): Form
    {
        $this->resource = $resource;

        return $this;
    }

    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    public function getEntry(): ?EntryContract
    {
        return $this->entry;
    }

    public function setEntry(EntryContract $entry): Form
    {
        $this->entry = $entry;

        return $this;
    }

    public function hasEntry(): bool
    {
        return (bool)$this->entry;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): Form
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    protected function provideFields($fields)
    {
        if ($fields instanceof ProvidesFields) {
            $fields = $fields->provideFields();
        }

        if (is_array($fields)) {
            $fields = collect($fields)->map(function ($field) {
                return is_array($field) ? FieldFactory::createFromArray($field, FormField::class) : $field;
            });
        }

        return $fields;
    }
}