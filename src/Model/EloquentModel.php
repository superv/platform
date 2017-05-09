<?php namespace SuperV\Platform\Model;

use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    protected $guarded = [];
    
    public $timestamps = false;
    
    public function getId()
    {
        return $this->getKey();
    }
}