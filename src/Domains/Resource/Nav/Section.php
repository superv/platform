<?php

namespace SuperV\Platform\Domains\Resource\Nav;

use Illuminate\Database\Eloquent\Collection;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Support\Composer\Payload;

class Section extends Entry
{
    protected $table = 'sv_navigation';

    /** @var \SuperV\Platform\Domains\Resource\Nav\Nav */
    protected $root;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Section $entry) {
            $entry->getChildren()->map->delete();
        });

        static::deleted(function (Section $entry) {
            // delete parent if it has no child except this
            //
            if ($parent = $entry->getParent()) {
                if ($parent->children()->count() === 0) {
                    $parent->delete();
                }
            }
        });
    }

    public function add(string $namespace): Section
    {
        return static::createFromString($namespace, $this);
    }

    public function compose($withColophon = false)
    {
        $colophon = $this->getColophon();

        $payload = new Payload([
            'title'    => $this->getTitle(),
            'titles'   => $this->addon ? sv_trans($this->addon.'::'.$this->title.'.label') : $this->title,
            'handle'   => $this->handle,
            'colophon' => $withColophon ? $colophon : null,
            'icon'     => $this->icon,
            'url'      => $this->url,
            'sections' => $sections = $this->children()
                                           ->with('children')
                                           ->get()
                                           ->map(function (Section $section) use ($withColophon) {
                                               return $section->setRoot($this->root)->compose($withColophon);
                                           })
                                           ->filter()
                                           ->keyBy('handle')
                                           ->all(),
        ]);

        if ($callbacks = (Nav::$callbacks[$colophon] ?? [])) {
            foreach ($callbacks as $callback) {
                app()->call($callback, ['payload' => $payload]);
            }
        }

        return $payload->get();
    }

    public function getColophon()
    {
        if (is_null($this->parent)) {
            return $this->handle;
        }

        if ($parentColophon = $this->parent->getColophon()) {
            return $parentColophon.'.'.$this->handle;
        }

        return $this->handle;
    }

    public function parent()
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function getParent(): ?Section
    {
        return $this->parent;
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

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setRoot(Nav $root): Section
    {
        $this->root = $root;

        return $this;
    }

    public static function get(string $handle, ?Section $parent = null): ?self
    {
        $count = count($parts = explode('.', $handle));
        if ($count === 1) {
            return $parent ? $parent->getChild($handle) : static::where('handle', $handle)->first();
        }
        // acp settings auth
        if (! $parent) {
            $parent = static::get(array_shift($parts));

            return static::get(implode('.', $parts), $parent);  // setting.auth, acp
        }

        $firstChild = $parent->getChild(array_shift($parts)); // find setting in acp

        return static::get(implode('.', $parts), $firstChild); // auth, setting
    }

    public static function createFromString(string $string, ?Section $parent = null): Section
    {
        $count = count($parts = explode('.', $string));
        if ($count === 1) {
            if ($parent) {
                return $parent->getChildOrCreate($parts[0]);
            }

            return static::where('handle', $parts[0])->firstOrCreate(static::make(null, $parts[0]));
        } else {
            $entry = static::createFromString(array_shift($parts), $parent);
            if (count($parts)) {
                static::createFromString(implode('.', $parts), $entry);
            }

            return $entry;
        }
    }

    public static function createFromArray(array $data): Section
    {
        $parent = array_pull($data, 'parent');
        $handle = ($parent ? $parent.'.' : '').$data['handle'] ?? str_slug($data['title'], '_');

        static::createFromString($handle);
        $section = static::get($handle);
        $section->update($data);

        return $section;
    }

    public static function make(?string $title = null, ?string $handle = null): array
    {
        return array_filter([
            'title'  => $title ?? ucwords(str_replace('_', ' ', $handle)),
            'handle' => $handle ?? str_slug(strtolower($title), '_'),
        ]);
    }
}
