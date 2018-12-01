<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Nav
{
    use FiresCallbacks;

    /**
     * @var Section
     */
    protected $entry;

    public function __construct(Section $entry)
    {
        $this->entry = $entry;
    }

    public function addSection(string $title): Section
    {
        return $this->entry->addChild($title);
    }

    public function getChild(string $handle)
    {
        return $this->entry->getChild($handle);
    }

    public function add(string $namespace): Section
    {
        return Section::createFromString($namespace, $this->entry);
    }

    public function compose()
    {
        return $this->entry->compose();
    }

    public function entry(): Section
    {
        return $this->entry;
    }

    public static function get(string $handle): Nav
    {
        if (! $entry = Section::get($handle)) {
            throw new PlatformException('Nav not found : '.$handle);
        }

        return new static($entry);
    }

    public static function create(string $handle): Nav
    {
        return new static(Section::createFromString($handle));
    }

    public static function createFromArray(array $array): Section
    {
        return Section::createFromArray($array);
    }
}