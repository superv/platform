<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
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
    protected $callbacks;

    public function addResource(Resource $resource)
    {
        $this->resources[] = $resource;
    }

    public function post(Request $request)
    {
        $this->callbacks = [];
        $this->fields->map(function (FieldType $field) use ($request) {
            $this->callbacks[] = $field->setValue($request->__get($field->getName()));
        });

        sv_collect($this->resources)->map(function (Resource $resource) { $resource->saveEntry(); });

        /**
         * Apply callbacks should run after the entry is created
         */
        collect($this->callbacks)->filter()->map(function (\Closure $callback) {
            $callback();
        });
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
        cache()->forever('sv:forms:'.$this->uuid(), serialize($this));
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

    public static function fromCache($uuid): ?Form
    {
        if ($form = cache('sv:forms:'.$uuid)) {
            return unserialize($form);
        }

        return null;
    }
}