<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FormField;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form as FormContract;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form implements FormContract, ProvidesUIComponent, Responsable
{
    use FiresCallbacks;

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

    protected $postSaveCallbacks = [];

    protected $title;

    public function make()
    {
        $this->uuid = uuid();

        if (is_null($this->fields)) {
            if ($this->entry) {
                $this->fields = $this->provideFields($this->entry);
            }
        }

        $this->fields->map(function (Field $field) {
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

    public function save(): self
    {
        // If this is a no-entry form then we will
        // have to validate on our own since the
        // model events wont fire
        //
        if (! $this->hasEntry()) {
            $this->validate();
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

        return $this;
    }

    public function validate()
    {
        $data = $this->request->all();
        $rules = $this->getFields()->map(function (Field $field) {
            return [$field->getName(), $this->parseFieldRules($field)];
        })->filter()->toAssoc()->all();

        app(Validator::class)->make($data, $rules);
    }

    public function parseFieldRules(Field $field)
    {
        $rules = $field->getRules();

        if ($field->isRequired()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return $rules;
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
        $this->fields->merge($fields);
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

    public function addFields($fields): self
    {
        $this->mergeFields($fields);

        return $this;
    }

    protected function provideFields($fields)
    {
        if ($fields instanceof ProvidesFields) {
            $fields = $fields->provideFields();
        }

        if (is_array($fields)) {
            $fields = collect($fields)->map(function ($field) {
                return is_array($field) ?  FieldFactory::createFromArray($field, FormField::class) : $field;
            });
        }

        return $fields;
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

    public function toResponse($request)
    {
        $action = $request->get('__form_action');

        if ($action === 'view') {
            $route = $this->resource->route('view.page', $this->getEntry());
        } elseif ($action === 'create_another') {
            $route = $this->resource->route('create');
        } elseif ($action === 'edit_next') {
            $next = $this->resource->newQuery()->where('id', '>', $this->getEntry()->getId())->first();
            if ($next) {
                $route = $this->resource->route('edit', $next).'?action=edit_next';
            }
        }

        return response()->json([
            'data' => [
                'action'      => $action,
                'redirect_to' => $route ?? $this->resource->route('index'),
            ],
        ]);
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function for($arg, $fields = null): self
    {
        if (is_string($arg)) {
            $resource = sv_resource($arg);
        }

        if ($arg instanceof EntryContract) {
            $entry = $arg;
            $resource = sv_resource($entry);
        }

        $resource = $resource ?? $arg;

        $form = new static();
        $form->setIdentifier($resource->getResourceKey());
        $form->setFields($fields ?? $resource->getFields());
        $form->setResource($resource);
        $form->setEntry($entry ?? $resource->newEntryInstance());

        return $form;
    }
}