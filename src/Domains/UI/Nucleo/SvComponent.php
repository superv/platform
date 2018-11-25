<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Auth\Access\Guard\Guardable;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Concerns\Hydratable;

class SvComponent implements Composable, Responsable, Guardable
{
    use Hydratable;

    protected $id;

    protected $name = 'sv-block-----';

    protected $url;

    protected $fetchOnCreated = true;

    /** @var \SuperV\Modules\Nucleo\Domains\Resource\Resource * */
    protected $resource;

    /** @var array */
    protected $props = [];

    protected $classList = [];

    protected $guardKey;

    /**
     * @param string|null $name
     * @return static
     */
    public static function make(string $name = null)
    {
        $component = new static;
        if ($name) {
            $component->name = $name;
        }
        $component->id = substr(md5(uniqid()), 0, 12);

        return $component;
    }

    public function props(): array
    {
        return $this->props ?: [];
    }

    public function setProps($props)
    {
        $this->props = $props instanceof Arrayable ? $props->toArray() : $props;

        return $this;
    }

    public function setProp($key, $value)
    {
        $this->props[$key] = $value;

        return $this;
    }

    public function getProp($key, $default = null)
    {
        return array_get($this->props, $key, $default);
    }

    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    public function build()
    {
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        $id = substr(md5(uniqid()), 0, 8);

        return array_filter([
            'id'        => $id,
            'url'       => $this->url,
            'component' => $this->name,
//            'props'     => array_merge($this->props(), ['block-id' => $id]),
            'props'     => $this->props(),
            'class'     => $this->getClassList(),
        ]);
    }

    public function toResponse($request)
    {
        return ['data' => $this->compose()];
    }

    /**
     * @param $class
     * @return static
     */
    public function class($class)
    {
        $this->classList[] = $class;

        return $this;
    }

    public function block($block)
    {
        if (is_string($block)) {
            $block = SvBlock::make($block);
        }

        return $this->setProp('block', $block);
    }

    public function toBlock()
    {
        return sv_block($this);
    }

    /**
     * @param bool $fetchOnCreated
     * @return static
     */
    public function setFetchOnCreated(bool $fetchOnCreated)
    {
        $this->setProp('fetchOnCreated', $fetchOnCreated);

        return $this;
    }

    /**
     * CSS methods
     */

    /**
     * Set margin right
     *
     * @param $value
     * @return static
     */
    public function mR($value)
    {
        return $this->class("mr-{$value}");
    }

    /**
     * Set width
     *
     * @param $value
     * @return static
     */
    public function w($value)
    {
        return $this->class("w-{$value}");
    }

    /**
     * Set padding
     *
     * @param $value
     * @return self
     */
    public function p($value)
    {
        return $this->class("p-{$value}");
    }

    public function card()
    {
        return $this->class('');
    }

    /**
     * @param mixed $resource
     * @return SvRelationTable
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    protected function getClassList()
    {
        return ! empty($this->classList) ? implode(' ', $this->classList) : null;
    }

    public function guardKey(): ?string
    {
        return $this->guardKey;
    }

    /**
     * @param mixed $guardKey
     * @return SvComponent
     */
    public function setGuardKey($guardKey)
    {
        $this->guardKey = $guardKey;

        return $this;
    }
}