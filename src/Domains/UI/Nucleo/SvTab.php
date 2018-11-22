<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

class SvTab
{
    protected $title;

    /** @var \SuperV\Platform\Domains\UI\Nucleo\SvComponent */
    protected $content;

    protected $fetch = false;

    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    public function content(SvComponent $content)
    {
        $this->content = $content;
        return $this;
    }

    public function setGuardKey($guardKey)
    {
        $this->content->setGuardKey($guardKey);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function autoFetch()
    {
        $this->fetch = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoFetch()
    {
        return $this->fetch;
    }
}