<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Prototype extends Model
{
    protected $table = 'nucleo_prototypes';

    protected static function boot()
    {
        static::deleted(function(Prototype $prototype) {
            $prototype->fields->map->delete();
        });
        parent::boot();
    }

    protected $guarded = [];

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function field($slug)
    {
        foreach ($this->fields as $field) {
            if ($field->slug == $slug) {
                return $field;
            }
        }
    }

    public function structs()
    {
        return $this->hasMany(Struct::class);
    }

    /**
     * @param $slug
     * @return self
     */
    public static function bySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function model()
    {
        return Nucleo::modelOfTable($this->slug);
    }
}