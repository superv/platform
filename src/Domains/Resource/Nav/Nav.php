<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use SuperV\Platform\Exceptions\PlatformException;

class Nav
{
    use ForwardsCalls;

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
//        return $this->entry->add($namespace);
    }

    public function build()
    {
        $this->sections = Section::query()
                                 ->where('nav', $this->handle)
                                 ->get()
                                 ->map(function ($entry) { return $entry->toArray(); })
                                 ->groupBy('section');

        $this->sections->transform(function (Collection $section) {
            return Section::create($section->groupBy('subsection'));
        });

        return $this;
    }

    public function compose()
    {
        return $this->entry->compose();
        $sections = $this->entry->children()->with('children')->get();

        return [
            'title'    => $this->entry->title,
            'handle'   => $this->entry->handle,
            'sections' => $sections
                ->map(function (Section $section) {
                    return $section->compose();
                })
                ->filter()
                ->all(),
        ];
    }

    public function sections(): Collection
    {
        return $this->sections;
    }

    public function section($key): Section
    {
        return $this->sections->get($key);
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