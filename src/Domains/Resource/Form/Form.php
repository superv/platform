<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form implements ProvidesUIComponent
{
    use FiresCallbacks;

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

            $fields->map(function (Field $field) use ($entry) {
                if ($field->isHidden() || ! $this->request->has($field->getColumnName())) {
                    return;
                }

                $this->postSaveCallbacks[] = $field->resolveRequestToEntry($this->request, $entry);

//                $requestValue = $this->request->__get($field->getColumnName());
//                if ($callback = $field->hydrateFromRequest($requestValue, $entry)) {
//                    $this->postSaveCallbacks[] = $callback;
//                }
            });
        }

        $this->notifyWatchers($this);

        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });

        return $this;
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

    public function compose(): Composition
    {
        $composition = new Composition([
            'url'    => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => $this->getFields()
                             ->filter(function (Field $field) { return ! $field->isHidden(); })
                             ->map(function (Field $field) { return $field->compose()->get(); })
                             ->values()
                             ->all(),
        ]);
        $this->fire('composed', ['form' => $this, 'composition' => $composition]);

        return $composition;
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

    public function hideFields(array $fields): self
    {
        collect($fields)->map(function ($fieldName) { $this->hideField($fieldName); });

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
            $fields = collect($fields);
        }

        return $fields;
    }

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        $this->groups[$handle] = $this->provideFields($fieldsProvider);

        $this->mergeFields($fieldsProvider, $watcher, $handle);

        return $this;
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\Field[]|Collection
     */
    public function getFields(): Collection
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

    public function getWatcher(?string $handle = 'default')
    {
        return $this->entries[$handle];
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(FormConfig $config): Form
    {
        return new static($config);
    }
}