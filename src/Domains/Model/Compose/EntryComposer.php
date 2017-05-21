<?php namespace Merpa\SupportModule\Compose;

use Anomaly\Streams\Platform\Entry\EntryModel;

class EntryComposer
{
    /** @var  EntryModel */
    protected $object;

    protected $fields = [];

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function id()
    {
        return $this->object->getKey();
    }

    public function toArray()
    {
        return $this->object->toArray();
    }

    public function compose($params)
    {
        $composed = [];
        foreach ($this->fields as $key) {
            $composed[$key] = $this->__get($key);
        }

        return $composed;
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

        throw new \InvalidArgumentException('Method not found: ' .$name);
    }

    public function __get($key)
    {
        $method = 'get' . studly_case($key);

        if (method_exists($this->object, $method)) {
            return call_user_func([$this->object, $method]);
        }

        return $this->object->getAttribute($key);

    }
}