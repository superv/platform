<?php

namespace SuperV\Platform\Domains\Media;

class MediaOptions
{
    protected $label;

    protected $disk = 'local';

    protected $path = '/';

    protected $visibility = 'private';

    public function __construct(?string $label = 'photo')
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $path
     * @return MediaOptions
     */
    public function path(?string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return MediaOptions
     */
    public function label(string $label)
    {
        $this->label = $label;

        return $this;
    }

    public function public()
    {
        return $this->visibility('public');
    }

    public function private()
    {
        return $this->visibility('private');
    }

    /**
     * @param string $visibility
     * @return MediaOptions
     */
    public function visibility(string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function all()
    {
        return [
            'label'      => $this->label,
            'disk'       => $this->disk,
            'path'       => $this->path,
            'visibility' => $this->visibility,
        ];
    }

    /**
     * @param string $disk
     * @return MediaOptions
     */
    public function disk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public static function one($label = null)
    {
        return new static($label);
    }
}