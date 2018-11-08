<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Jobs\PostForm;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Form
{
    use FiresCallbacks;

    protected $uuid;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $entries;

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
        $this->entries = collect();
    }

    public function addEntry(Entry $entry)
    {
        $this->entries->push($entry);

        return $this;
    }


    public function post(Request $request)
    {
        PostForm::dispatch($this, $request);
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


    public function isBuilt(): bool
    {
        return $this->built;
    }

    /**
     * @param bool $built
     */
    public function setBuilt(bool $built): void
    {
        $this->built = $built;
    }

    public function getEntries(): \Illuminate\Support\Collection
    {
        return $this->entries;
    }

    public static function make(): self
    {
        $form = new static;

        $form->uuid = Str::uuid();

        $form->url = sv_url('sv/forms/'.$form->uuid());

        return $form;
    }

    public static function fromCache($uuid): ?Form
    {
        if ($form = cache('sv:forms:'.$uuid)) {
            return unserialize($form);
        }

        return null;
    }
}