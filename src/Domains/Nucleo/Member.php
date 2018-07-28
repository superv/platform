<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $guarded = [];

    protected $table = 'nucleo_members';

    protected static function boot()
    {
        static::deleted(function (Member $member) {
            $member->values->map->delete();
        });
        parent::boot();
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function setValue($value)
    {
        $valueEntry = $this->values()->create([
            'value'      => $value,
            'created_by' => \Auth::id(),
        ]);

        $this->update([
            'value_id' => $valueEntry->id,
        ]);
    }

    public function getValue()
    {
        return $this->value->value;
    }

    public function values()
    {
        return $this->hasMany(Value::class)->orderBy('id', 'DESC');
    }

    public function value()
    {
        return $this->belongsTo(Value::class);
    }
}