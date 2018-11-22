<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

class SvCard extends SvComponent
{
    protected $name = 'sv-card';

    protected $block;

    protected $title;

    protected $subtitle;

    protected $actions;

    /**
     * @param mixed $block
     * @return SvCard
     */
    public function block($block)
    {
        $this->block = $block;

        return $this;
    }

    public function props(): array
    {
        return array_merge([
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            'block'    => $this->block,
            'actions'  => $this->actions,
        ]);
    }

    /**
     * @param mixed $actions
     * @return SvCard
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * @param mixed $title
     * @return SvCard
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param mixed $subtitle
     * @return SvCard
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }
}