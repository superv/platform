<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form
{
    use FiresCallbacks;

    protected $uuid;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $resources;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    protected $url;

    protected $method = 'post';

    /** @var array */
    protected $postSaveCallbacks = [];

    /** @var bool */
    protected $built = false;

    public function __construct()
    {
        $this->fields = collect();
        $this->resources = collect();
    }

    public function addResource(Resource $resource)
    {
        $this->resources->push($resource);

        return $this;
    }

    public function build(): self
    {
        if ($this->isBuilt()) {
            throw new PlatformException('Form is already built.');
        }
        $this->resources->map->build();

        $this->uuid = Str::uuid();

        // first add all fields from all resources
        $this->resources->map(function (Resource $resource) {
            $resource->copyFreshFields()
                     ->map(function (Field $field) {
                         $this->addField($field);
                     });
        });
        $this->fire('building.fields', ['form' => $this]);
        $this->fields->map->build();

        // build Url
        $this->url = sv_url('sv/forms/'.$this->uuid());

        $this->built = true;

        $this->cache();

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function cache()
    {
        $this->callbacks = [];
        cache()->forever($this->cacheKey(), serialize($this));
    }

    protected function cacheKey(): string
    {
        return 'sv:forms:'.$this->uuid();
    }

    public function compose(): FormData
    {
        return FormData::make($this);
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function removeField(Closure $callback)
    {
        $this->fields = $this->fields->filter(function (Field $field) use ($callback) {
            return ! $callback($field);
        })->values();
    }

    public function removeFieldBeforeBuild(Closure $callback)
    {
        $this->on('building.fields', function (Form $form) use ($callback) {
            $form->removeField($callback);
        });
    }

    public function addField(Field $field)
    {
        $this->fields->push($field);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function post(Request $request)
    {
        $this->setFieldValues($request);

        $this->resources->map(function (Resource $resource) {
            $resource->saveEntry(['form' => $this]);
        });

        $this->applyPostSaveCallbacks();
    }

    protected function applyPostSaveCallbacks(): void
    {
        collect($this->postSaveCallbacks)->filter()->map(function (\Closure $callback) {
            $callback();
        });
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    protected function setFieldValues(Request $request): void
    {
        $this->fields->map(function (Field $field) use ($request) {
            $this->postSaveCallbacks[] = $field->setValueFromRequest($request);
        });
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    public static function of(Resource $resource): self
    {
        return app(Form::class)->addResource($resource);
    }

    public static function fromCache($uuid): ?Form
    {
        if ($form = cache('sv:forms:'.$uuid)) {
            return unserialize($form);
        }

        return null;
    }
}