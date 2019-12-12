<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

class AnonymousModel extends ResourceEntry
{
    public $timestamps = false;

//    public function setTable($table)
//    {
//        return $this->table = $table;
//    }

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @param bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array)$attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());
        $model->setKeyName($this->getKeyName());
        $model->setResourceIdentifier($this->getResourceIdentifier());

        return $model;
    }
}
