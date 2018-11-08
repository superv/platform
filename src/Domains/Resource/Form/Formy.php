<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Str;

class Formy
{
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

    public function __construct(array $fields)
    {
        $this->fields = $fields;
        $this->uuid = Str::uuid()->toString();
        $this->url = sv_url('sv/forms/'.$this->uuid);
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function compose(): array
    {
        return [
            'url' => $this->getUrl(),
            'method' => $this->getMethod(),
            'fields' => collect($this->getFields())->map->compose()->all()
        ];
    }

    public function getFields(): array
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
}