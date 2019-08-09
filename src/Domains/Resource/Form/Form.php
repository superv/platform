<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form as FormContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
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

    protected $isMade = false;

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

        $this->fields->map(function (FormField $field) {
            $field->setForm($this);

            if (in_array($field->getColumnName(), $this->getHiddenFields())) {
                $field->hide();
            }

            if ($this->hasEntry()) {
                $field->fillFromEntry($this->getEntry());
            }
        });

        $this->isMade = true;

        return $this;
    }

    public function save(): FormResponse
    {
        $this->setFormMode();

        $this->applyExtensionCallbacks();

        $this->validateTemporalFields();

        $this->fireBeforeSavingCallbacks();

        // If this is a no-entry form then we will
        // have to validate on our own since the
        // model events wont fire
        //
        if (! $this->hasEntry()) {
            $this->validate();
        }

        $this->resolveFieldValuesFromRequest();

        if ($this->hasEntry()) {
            $this->getEntry()->save();
        }

        $this->runPostSaveCallbacks();

        return new FormResponse($this, $this->resource, $this->getEntry());
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function hideField(string $fieldName): FormContract
    {
        if (! $field = $this->getField($fieldName)) {
            throw new Exception('Field not found: '.$fieldName);
        }

        $field->hide();

        $this->hiddenFields[] = $fieldName;

        return $this;
    }

    public function isUpdating()
    {
        return $this->mode === Form::MODE_UPDATE;
    }

    public function isCreating()
    {
        return $this->mode === Form::MODE_CREATE;
    }

    public function validate()
    {
        ValidateForm::dispatch($this->getFields(), $this->request->all());
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

    public function hideFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFields()->map(function (FormField $field) use ($fields) {
            if (in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function onlyFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFields()->map(function (FormField $field) use ($fields) {
            if (! in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function addField(FormField $field)
    {
//        if (is_array($field)) {
//            $field = new FormField(FieldFactory::createFromArray($field));
//        }
        // Fields added on the fly should be marked as temporal
        //
        $field->setTemporal(true);

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
        if ($this->isMade) {
            throw new Exception('Can not set fields after form is made');
        }
        $this->fields = $this->provideFields($fields);

        return $this;
    }

    public function getField(string $name): ?FormField
    {
        return $this->fields->first(
            function (FormField $field) use ($name) {
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

    public function setRequest(?Request $request): self
    {
        if ($request) {
            $this->request = $request;
        }

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
            $fields = collect($fields)
                ->map(function ($field) {
                    $field = is_array($field) ? FieldFactory::createFromArray($field) : $field;

                    return $field;
                });
        }

        return wrap_collect($fields);
    }

    protected function validateTemporalFields(): void
    {
        $temporalFields = $this->fields->filter(function (FormField $field) {
            return $field->isTemporal();
        });

        ValidateForm::dispatch($temporalFields, $this->request->all());
    }

    protected function applyExtensionCallbacks(): void
    {
        if ($this->isCreating()) {
            if ($this->resource && $callback = $this->resource->getCallback('creating')) {
                app()->call($callback, ['form' => $this]);
            }
        }

        if ($this->isUpdating()) {
            if ($this->resource && $callback = $this->resource->getCallback('editing')) {
                app()->call($callback, ['form' => $this, 'entry' => $this->getEntry()]);
            }
        }
    }

    protected function setFormMode(): void
    {
        if ($this->hasEntry() && $this->entry->exists) {
            $this->mode = Form::MODE_UPDATE;
        }
    }

    protected function fireBeforeSavingCallbacks(): void
    {
        $this->fields->map(function (FormField $field) {
            if ($field->isHidden() && ! $field->isTemporal()) {
                return;
            }

            $field->fire('before.saving', ['request' => $this->request]);
        });
    }

    protected function resolveFieldValuesFromRequest(): void
    {
        $this->fields->map(function (FormField $field) {
            if ($field->isHidden() && ! $field->isTemporal() || $field->isTemporal()) {
                return;
            }

            $this->postSaveCallbacks[] = $field->resolveRequest($this->request, $this->getEntry());
        });
    }

    protected function runPostSaveCallbacks(): void
    {
        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });
    }
}
