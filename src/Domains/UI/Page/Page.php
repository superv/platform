<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\UI\Components\PageComponent;
use SuperV\Platform\Domains\UI\Components\UIComponent;

class Page implements ProvidesUIComponent, Responsable
{
    protected $uuid;

    protected $meta = [];

    protected $blocks = [];

    protected $actions = [];

    protected $tokens;

    protected $component;

    protected function __construct(string $title)
    {
        $this->boot();
        $this->meta['title'] = $title;
    }

    protected function boot()
    {
        $this->uuid = uuid();
    }

    public function build($tokens)
    {
        $this->tokens = $tokens;

        $this->component = $this->makeComponent();

        $this->component->getProps()->transform(function ($prop) {
            if (is_array($prop)) {
                foreach ($prop as $key => $value) {
                    if ($value instanceof ProvidesUIComponent) {
                        $prop[$key] = $value->makeComponent();
                    }
                }
            }

            return $prop;
        });

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json([
            'data' => sv_compose($this->component, $this->tokens),
        ]);
    }

    public function addBlock($block)
    {
        $this->blocks[] = $block;

        return $this;
    }

    public function addBlocks(array $blocks = [])
    {
        $this->blocks = array_merge($this->blocks, $blocks);

        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function makeComponent(): UIComponent
    {
        return PageComponent::from($this);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta($key, $value)
    {
        array_set($this->meta, $key, $value);

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions($actions): Page
    {
        $this->actions = $actions;

        return $this;
    }

    public function addAction($action)
    {
        $this->actions[] = $action;

        return $this;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(string $title)
    {
        return new static($title);
    }
}

