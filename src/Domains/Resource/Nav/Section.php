<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use Illuminate\Database\Eloquent\Collection;
use SuperV\Platform\Domains\Resource\Model\EntryModel;

class Section extends EntryModel
{
    protected $table = 'sv_navigation';

    public function add(string $namespace): Section
    {
        return static::createFromString($namespace, $this);
    }

    public function compose()
    {
        return array_filter([
            'title'    => $this->title,
            'handle'   => $this->handle,
            'sections' => $this->children()
                               ->with('children')
                               ->get()
                               ->map(function (Section $section) { return $section->compose(); })
                               ->filter()->all(),
        ]);
    }

//    public function add(string $namespace): Section
//    {
//        $count = count($parts = explode('.', $namespace));
//        if ($count === 1) {
//            return $this->getChildOrCreate($parts[0]);
//        } else {
//            $parent = $this->getChildOrCreate(array_shift($parts));
//            if (count($parts)) {
//                $parent->add(implode('.', $parts));
//            }
//
//            return $parent;
//        }
//    }

    public function parent()
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Section::class, 'parent_id');
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getChild(string $handle): Section
    {
        return $this->children()->where('handle', $handle)->first();
    }

    public function getChildOrCreate(string $handle): Section
    {
        return $this->children()->where('handle', $handle)->firstOrCreate(static::make(null, $handle));
    }

    public function addChild($title): Section
    {
        return $this->children()->create(static::make($title));
    }

    public static function get(string $handle): self
    {
        return static::where('handle', $handle)->first();
    }

    public static function createFromString(string $string, ?Section $parent = null): Section
    {
        $count = count($parts = explode('.', $string));
        if ($count === 1) {
            if ($parent) {
                return $parent->getChildOrCreate($parts[0]);
            }

            return static::where('handle', $parts[0])->firstOrCreate(static::make(null, $parts[0]));
//            return $parent ? $parent->getChildOrCreate($handle) : static::create(static::make(null, $handle));
        } else {
            $entry = static::createFromString(array_shift($parts), $parent);
//            $entry =  $parent ? $parent->getChildOrCreate(array_shift($parts)) : static::createFromString(array_shift($parts));
//            $entry =  $parent ? $parent->getChildOrCreate(array_shift($parts)) : static::create(static::make(null, array_shift($parts)));
            if (count($parts)) {
                static::createFromString(implode('.', $parts), $entry);
//                $entry->add(implode('.', $parts));
            }

            return $entry;
        }
    }

    public static function make(?string $title = null, ?string $handle = null): array
    {
        return array_filter([
            'title'  => $title ?? ucwords(str_replace('_', ' ', $handle)),
            'handle' => $handle ?? str_slug(strtolower($title), '_'),
        ]);
    }
}