<?php namespace SuperV\Platform\Support\Composer;

abstract class EntryComposer
{
    /** @var  \Illuminate\Database\Eloquent\Model */
    protected $object;

    protected $fields;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function compose($params)
    {
        if ($this->fields) {
            return array_intersect_key($this->object->toArray(), array_flip($this->fields));
        }

        return $this->object->toArray();
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        if (method_exists($this->object, $name)) {
            return call_user_func_array([$this->object, $name], $arguments);
        }

        if (starts_with($name, 'get')) {
            return $this->object->getAttribute(snake_case(str_replace_first('get', '', $name)));
        }

        throw new \InvalidArgumentException('Method not found: '.$name);
    }

    public function __get($key)
    {
        $method = 'get'.studly_case($key);

        if (method_exists($this->object, $method)) {
            return call_user_func([$this->object, $method]);
        }

        return $this->object->getAttribute($key);
    }

    public function id()
    {
        return $this->object->getKey();
    }

    public function toArray()
    {
        return $this->object->toArray();
    }
}