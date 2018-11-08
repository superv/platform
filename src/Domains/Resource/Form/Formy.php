<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Concerns\Watchable;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Exceptions\PlatformException;

class Formy
{
    use Watchable;

    /**
     * @var array
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

    protected $isBooted = false;

    public function __construct(array $fields)
    {
        $this->fields = collect($fields);
    }

    //
    //      < M U T A T O R   M E T H O D S >
    //

    public function boot(): self
    {
        $this->uuid = Str::uuid()->toString();
        $this->url = sv_url('sv/forms/'.$this->uuid);

        $this->isBooted = true;

        $this->cache();

        return $this;
    }

    public function save()
    {
        if (! $this->isBooted) {
            PlatformException::fail('Form is not booted yet.');
        }

        $this->fields()->map(function (Field $field) {
            $field->setValue($this->request->__get($field->getName()));
        });

        $this->notifyWatchers($this);
    }

    public function request(Request $request)
    {
        $this->request = $request;
    }

    //
    //      </ M U T A T O R   M E T H O D S >
    //

    public function uuid(): string
    {
        return $this->uuid;
    }


    public function compose(): array
    {
        return [
            'url'    => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => $this->fields()->map->compose()->all(),
        ];
    }

    public function fields(): Collection
    {
        return $this->fields;
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
        if (! $this->isBooted) {
            PlatformException::fail('Form is not booted yet.');
        }

        cache()->forever($this->cacheKey($this->uuid()), serialize($this));
    }

    public static function cacheKeyPrefix()
    {
        return 'sv:forms';
    }

    public static function cacheKey(?string $uuid = null): string
    {
        return static::cacheKeyPrefix().str_prefix($uuid, ':');
    }

    public static function wakeup($uuid): ?self
    {
        if ($form = cache(static::cacheKey($uuid))) {
            return unserialize($form);
        }

        return null;
    }
}