<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldsProvider;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Exceptions\PlatformException;

class Form
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fieldTypes;

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

    /**
     * @var bool
     */
    protected $booted = false;

    protected $watchers = [];

    public function __construct(array $fields = [])
    {
        $this->fields = collect($fields);
        $this->uuid = uuid();
        $this->url = sv_url('sv/forms/'.$this->uuid);
    }

    public function boot()
    {
        if ($this->booted) {
            PlatformException::fail("Form already booted");
        }

        $this->booted = true;

        // Make field type and tell them to watch the fields
        $this->getFields()->map(function (Field $field) {
//            FieldType::fromField($field);
        });
    }

    public function save(): self
    {
        $this->ensureBooted();

        $this->getFields()->map(function (Field $field) {
            $field->setValue($this->request->__get($field->getName()));
        });

        $this->notifyWatchers($this);

        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function addGroups(Collection $groups)
    {
        $groups->map(function (Group $group) {
            $this->fields = $this->fields->merge($group->getFields());
            if ($group->getWatcher()) {
                $this->addWatcher($group->getHandle(), $group->getWatcher());
            }
        });
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    //
    //      <!---  M U T A T O R   M E T H O D S   E N D S  H E R E  --->
    //

    public function compose(): array
    {
        return [
            'url'    => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => $this->getFields()->map->compose()->all(),
        ];
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getField(string $name): Field
    {
        return $this->getFields()
                    ->first(
                        function (Field $field) use ($name) {
                            return $field->getName() === $name;
                        });
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function cache()
    {
        cache()->forever($this->cacheKey($this->uuid()), serialize($this));
    }

    public function ensureBooted(): void
    {
        if (! $this->booted) {
            PlatformException::fail('Form is not booted yet.');
        }
    }

    public function getWatcher($handle)
    {
        return $this->watchers[$handle];
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
        if ($form = cache(static::cacheKey($uuid))) {
            return unserialize($form);
        }

        return null;
    }

    public static function of(FieldsProvider $provider): Form
    {
        $form = (new static($provider->provide()));

        return $form;
    }
}