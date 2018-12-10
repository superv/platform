<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form implements ProvidesUIComponent, Responsable
{
    use FiresCallbacks;

    /** @var Resource */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field[]|Collection
     */
    protected $fields;

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

    protected $entries = [];

    protected $postSaveCallbacks = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormConfig
     */
    protected $config;

    protected $groups;

    protected function __construct(FormConfig $config)
    {
        $this->config = $config;
        $this->fields = collect();
        $this->uuid = uuid();

        $this->boot();
    }

    protected function boot()
    {
        foreach ($this->config->getGroups() as $handle => $group) {
            $this->addGroup($group['provider'], $group['watcher'], $handle);
        }

        foreach ($this->entries as $handle => $entry) {
            $this->fields[$handle]->map(function (Field $field) use ($entry) {
                $field->setWatcher($entry);

                if (in_array($field->getColumnName(), $this->config->getHiddenFields())) {
                    $field->hide();
                }

                $field->fillFromEntry($entry);
            });
        }
    }

    public function save(): self
    {
        foreach ($this->groups as $handle => $fields) {
            $entry = $this->entries[$handle] ?? null;

            // If this is a no-entry form then we will
            // have to validate on our own since the
            // model events wont fire
            //
            if (! $entry) {
                $this->validate();
            }

            $fields->map(function (Field $field) use ($entry) {
                if ($field->isHidden()) {
                    return;
                }

                $this->postSaveCallbacks[] = $field->resolveRequest($this->request, $entry);
            });
        }

        $this->notifyWatchers($this);

        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });

        return $this;
    }

    public function validate()
    {
        $data = $this->request->all();
        $rules = $this->getFieldsFlat()->map(function (Field $field) {
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

    public function addWatcher($handle, Watcher $watcher)
    {
        $this->entries[$handle] = $watcher;

        return $this;
    }

    public function removeWatcher(Watcher $detach)
    {
        $this->entries = collect($this->entries)->filter(function (Watcher $watcher) use ($detach) {
            return $watcher !== $detach;
        })->filter()->values()->all();

        return $this;
    }

    public function notifyWatchers($params = null)
    {
        collect($this->entries)->map(function (Watcher $watcher) use ($params) {
            $watcher->save();
        });
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
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
//        return FormComponent::from($this);
    }

    public function mergeFields($fields, ?Watcher $watcher, string $handle = 'default')
    {
        $fields = $this->provideFields($fields);
        $this->fields->put($handle, $fields);

        if ($watcher) {
            $this->addWatcher($handle, $watcher);
        }
    }

    public function hideField(string $fieldName): self
    {
        if (! $field = $this->getField($fieldName)) {
            throw new Exception('Field not found: '.$fieldName);
        }

        $field->hide();

        return $this;
    }

    public function hideFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFieldsFlat()->map(function (Field $field) use ($fields) {
            if (in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function onlyFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->getFieldsFlat()->map(function (Field $field) use ($fields) {
            if (! in_array($field->getName(), $fields)) {
                $field->hide();
            }
        });

        return $this;
    }

    public function addFields($fields): self
    {
        $this->mergeFields($fields, null, 'default');

        return $this;
    }

    protected function provideFields($fields)
    {
        if ($fields instanceof ProvidesFields) {
            $fields = $fields->provideFields();
        }

        if (is_array($fields)) {
            $fields = collect($fields)->map(function ($field) {
                return is_array($field) ? sv_field($field) : $field;
            });
        }

        return $fields;
    }

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        $this->groups[$handle] = $this->provideFields($fieldsProvider);

        $this->mergeFields($fieldsProvider, $watcher, $handle);

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\Field[]|Collection
     */
    public function getFieldsFlat(): Collection
    {
        return $this->fields->flatten(1);
    }

    public function getField(string $name, $group = 'default'): ?Field
    {
        return $this->fields->get($group)
                            ->first(
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
        return $this->url ?? $this->config->getUrl();
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

    public function getEntryForHandle(?string $handle = 'default')
    {
        return $this->entries[$handle] ?? null;
    }

    public function getDefaultEntry(): ?EntryContract
    {
        return $this->getEntryForHandle('default');
    }

    public function setResource(Resource $resource): Form
    {
        $this->resource = $resource;

        return $this;
    }

    public function toResponse($request)
    {
        $action = $request->get('__form_action');

        if ($action === 'view') {
            $route = $this->resource->route('view', $this->getDefaultEntry());
        } elseif ($action === 'create_another') {
            $route = $this->resource->route('create');
        } elseif ($action === 'edit_next') {
            $next = $this->resource->newQuery()->where('id', '>', $this->getDefaultEntry()->getId())->first();
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(FormConfig $config): Form
    {
        return new static($config);
    }

    public static function forResource(Resource $resource): Form
    {
        return FormConfig::make($resource->newEntryInstance())
                         ->makeForm()
                         ->setResource($resource);
    }
}