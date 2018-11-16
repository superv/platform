<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldsProvider;
use SuperV\Platform\Domains\Resource\Field\Watcher;

class Form
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Field[]|Collection
     */
    protected $fields;

    /** @var \SuperV\Platform\Domains\Resource\Form\Group[]|Collection */
    protected $groups;

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

    protected $skipFields = [];

    protected $watchers = [];

    protected $postSaveCallbacks = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormConfig
     */
    protected $config;

    protected function __construct(FormConfig $config)
    {
        $this->config = $config;
        $this->groups = collect();
        $this->fields = collect();

        $this->boot();
    }

    protected function boot()
    {
        foreach($this->config->getGroups() as $handle => $group) {
            $this->addGroup($group['provider'], $group['watcher'], $handle);
        }

        foreach ($this->watchers as $handle => $watcher) {
            $this->fields[$handle]->map(function (Field $field) use ($watcher) {
                $field->setWatcher($watcher);
                $field->build();

                $field->initValue($watcher->getAttribute($field->getColumnName()));
            });
        }
    }

    public function save(): self
    {
        $this->getFields()->map(function (Field $field) {
            $this->postSaveCallbacks[] = $field->setValue($this->request->__get($field->getColumnName()));
        });

        $this->notifyWatchers($this);

        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });

        return $this;
    }

    public function addWatcher($handle, Watcher $watcher)
    {
        $this->watchers[$handle] = $watcher;

        return $this;
    }

    public function removeWatcher(Watcher $detach)
    {
        $this->watchers = collect($this->watchers)->filter(function (Watcher $watcher) use ($detach) {
            return $watcher !== $detach;
        })->filter()->values()->all();

        return $this;
    }

    public function notifyWatchers($params = null)
    {
        collect($this->watchers)->map(function (Watcher $watcher) use ($params) {
            $watcher->save();
        });
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function compose(): array
    {
        return [
            'url'    => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => $this->getFields()
                             ->map(function (Field $field) { return $field->compose()->get(); })
                             ->all(),
        ];
    }

    public function mergeFields($fields, ?Watcher $watcher, string $handle = 'default')
    {
        $fields = $this->provideFields($fields);
        $this->fields->put($handle, $fields);

        if ($watcher) {
            $this->addWatcher($handle, $watcher);

            // bunu boota al
//            $fields->map(function (Field $field) use ($watcher) {
//                $field->initValue($watcher->getAttribute($field->getColumnName()));
//            });
        }
    }

    public function removeField(string $name)
    {
        return $this->removeFields([$name]);
    }

    public function removeFields(array $skipFields)
    {
        $this->fields = $this->fields->map(function (Collection $fields) use ($skipFields) {
            return $fields->filter(function (Field $field) use ($skipFields) {
                return ! in_array($field->getName(), $skipFields);
            });
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
            $fields = collect($fields);
        }

        return $fields;
    }

    public function addGroup($fieldsProvider, Watcher $watcher = null, string $handle = 'default'): self
    {
        $this->mergeFields($fieldsProvider, $watcher, $handle);

        return $this;
    }

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

    public function getUrl(): string
    {
        return $this->config->getUrl();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function sleep()
    {
        cache()->forever($this->cacheKey($this->uuid()), serialize($this));

        return $this;
    }

    protected function washFace()
    {
//        foreach ($this->watchers as $handle => $watcher) {
//            $this->fields[$handle]->map(function (Field $field) use ($watcher) {
//                $field->setWatcher($watcher);
//                $field->build();
//            });
//        }
    }

    public function getWatcher(?string $handle = 'default')
    {
        return $this->watchers[$handle];
    }


    public function uuid(): string
    {
        return $this->config->uuid();
    }

    public static function cacheKeyPrefix()
    {
        return 'sv:forms';
    }

    public static function cacheKey(string $uuid): string
    {
        return static::cacheKeyPrefix().':'.$uuid;
    }

    public static function wakeup($uuid): ?self
    {
        /** @var \SuperV\Platform\Domains\Resource\Form\Form $form */
        if ($form = cache(static::cacheKey($uuid))) {
            return unserialize($form);
        }

        $form->washFace();

        return null;
    }

    public static function make(FormConfig $config): Form
    {
        return new static($config);
    }
}