<?php namespace SuperV\Platform\Domains\UI\Button;

use Illuminate\View\Factory;

class Button
{
    protected $button;

    protected $attributes;

    protected $text;

    protected $icon;

    protected $class;

    protected $type = 'default';

    protected $size = 'sm';

    /**
     * @var Factory
     */
    private $view;

    protected $iconOnly = false;

    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function render()
    {
        return $this->view->make('superv::button.button', ['button' => $this]);
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
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
}