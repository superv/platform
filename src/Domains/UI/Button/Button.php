<?php

namespace SuperV\Platform\Domains\UI\Button;

use Illuminate\View\Factory;

class Button
{
    protected $button;

    protected $attributes;

    protected $text;

    protected $title;

    protected $icon;

    protected $class;

    protected $type = 'default';

    protected $size = '';

    protected $iconOnly = false;

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function render($params = [])
    {
        if ($size = array_get($params, 'size')) {
            $this->setSize($size);
        }

        if ($class = array_get($params, 'class')) {
            $this->setClass($class);
        }

        return $this->view->make('superv::button.button', ['button' => $this]);
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     *
     * @return Button
     */
    public function setSize(string $size): Button
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * @param mixed $button
     *
     * @return Button
     */
    public function setButton($button)
    {
        $this->button = $button;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     *
     * @return Button
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     *
     * @return Button
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     *
     * @return Button
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return Button
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Button
     */
    public function setType(string $type): Button
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIconOnly(): bool
    {
        return $this->iconOnly;
    }

    /**
     * @param bool $iconOnly
     *
     * @return $this
     */
    public function setIconOnly(bool $iconOnly)
    {
        $this->iconOnly = $iconOnly;

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
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
