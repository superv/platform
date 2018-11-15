<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldsProvider;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Exceptions\PlatformException;

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

    /**
     * @var bool
     */
    protected $booted = false;

    protected $watchers = [];

    protected $postSaveCallbacks = [];

    public function __construct(array $fields = [])
    {
        $this->groups = collect();
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
//        $this->getFields()->map(function (Field $field) {
////            FieldType::fromField($field);
//        });
//

    }

    public function save(): self
    {
        $this->ensureBooted();

        $this->getFields()->map(function (Field $field) {
            $this->postSaveCallbacks[] = $field->setValue($this->request->__get($field->getName()));
        });

        $this->notifyWatchers($this);

        collect($this->postSaveCallbacks)->filter()->map(function (Closure $callback) {
            $callback();
        });

        return $this;
    }

    public function wakeup()
    {
        foreach ($this->watchers as $handle => $watcher) {
            $this->fields[$handle]->map(function (Field $field) use ($watcher) {
                $field->setWatcher($watcher);
                $field->build();
            });
        }
    }

    public function mergeFields(Collection $fields, ?Watcher $watcher, string $handle = 'default')
    {
        $this->fields->put($handle, $fields);

        if ($watcher) {
//            if ($watcher instanceof Entry) {
//                $watcher = new ResourceEntry($watcher);
//            }
            $this->addWatcher($handle, $watcher);

            $fields->map(function (Field $field) use ($watcher) {
                $field->setValue($watcher->getAttribute($field->getName()), false);
            });
        }
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

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    //
    //      <!---  M U T A T O R   M E T H O D S   E N D S  H E R E  --->
    //

    public function compose(): array
    {
        return [
            'url'    => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => $this->fields->flatten(1)->map->compose()->all(),
        ];
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
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function sleep()
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function cacheKeyPrefix()
    {
        return 'sv:forms';
    }

    public static function cacheKey(string $uuid): string
    {
        return static::cacheKeyPrefix().':'.$uuid;
    }

    public static function fromCache($uuid): ?self
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