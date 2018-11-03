<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Collection;

class Form
{
    protected $uuid;

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var Collection
     */
    protected $fields;

    protected $url;

    protected $method = 'post';

    /** @var array */
    protected $callbacks = [];

    public function addResource(Resource $resource)
    {
        $this->resources[] = $resource;
    }

    public function build(): self
    {
        $this->uuid = Str::uuid();

        $this->fields = new FormFields;

        // build Fields
        foreach ($this->resources as $resource) {
            $this->fields->mergeFrom($resource);
        }

        // build Url
        $this->url = sv_url('sv/forms/'.$this->uuid());

        $this->cache();

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public function cache()
    {
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

        $this->saveResources();

        $this->applyPostSaveCallbacks();
    }

    protected function applyPostSaveCallbacks(): void
    {
        collect($this->callbacks)->filter()->map(function (\Closure $callback) {
            $callback();
        });
    }

    protected function saveResources(): void
    {
        sv_collect($this->resources)->map(function (Resource $resource) { $resource->saveEntry(); });
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    protected function setFieldValues(Request $request): void
    {
        $this->fields->map(function (Field $field) use ($request) {
            $this->callbacks[] = $field->setValueFromRequest($request);
        });
    }


    public static function fromCache($uuid): ?Form
    {
        if ($form = cache('sv:forms:'.$uuid)) {
            return unserialize($form);
        }

        return null;
    }
}