<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use Closure;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Payload;

class Nav
{
    /**
     * @var Section
     */
    protected $entry;

    public static $callbacks = [];

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

    public function compose($withColophon = false)
    {
        return $this->entry->setRoot($this)->compose($withColophon);
    }

    public function entry(): Section
    {
        return $this->entry;
    }

    /**
     * @param                $sectionHandle
     * @param string|Closure $title
     * @param null           $url
     */
    public static function building($sectionHandle, $title, $url = null)
    {
        if ($title instanceof Closure) {
            $callback = $title;
        } else {
            $callback = function (Payload $payload) use ($url, $title) {
                $payload->push('sections', [
                    'title' => $title,
                    'url'   => $url,
                ]);
            };
        }
        $sectionCallbacks = static::$callbacks[$sectionHandle] ?? [];
        $sectionCallbacks[] = $callback;
        static::$callbacks[$sectionHandle] = $sectionCallbacks;
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