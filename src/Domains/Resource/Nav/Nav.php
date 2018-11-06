<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use Illuminate\Support\Collection;

class Nav
{
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
        return [
            'sections' => $this->sections->toArray(),
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

    public static function create(string $namespace)
    {
        return new static(Section::createFromString($namespace));
    }
}