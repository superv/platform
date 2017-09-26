<?php

namespace SuperV\Platform\Support;

use Illuminate\Foundation\Bus\DispatchesJobs;

class Presenter extends \Robbo\Presenter\Presenter
{
    use DispatchesJobs;

    protected $protected = [
        'delete',
        'save',
        'update',
    ];

    public function getObject()
    {
        return $this->object;
    }

    public function __get($var)
    {
        if (in_array($var, $this->protected)) {
            return null;
        }

        if ($method = $this->getPresenterMethodFromVariable($var)) {
            return $this->$method();
        }

        // Check the presenter for a getter.
        if (method_exists($this, camel_case('get_'.$var))) {
            return call_user_func_array([$this, camel_case('get_'.$var)], []);
        }

        // Check the presenter for a getter.
        if (method_exists($this, camel_case('is_'.$var))) {
            return call_user_func_array([$this, camel_case('is_'.$var)], []);
        }

        // Check the presenter for a method.
        if (method_exists($this, camel_case($var))) {
            return call_user_func_array([$this->object, camel_case($var)], []);
        }

        // Check the object for a getter.
        if (method_exists($this->object, camel_case('get_'.$var))) {
            return call_user_func_array([$this->object, camel_case('get_'.$var)], []);
        }

        // Check the object for a getter.
        if (method_exists($this->object, camel_case('is_'.$var))) {
            return call_user_func_array([$this->object, camel_case('is_'.$var)], []);
        }

        // Check the object for a method.
        if (method_exists($this->object, camel_case($var))) {
            return call_user_func_array([$this->object, camel_case($var)], []);
        }

        // Check the for a getter style hook.
        if (method_exists($this->object, 'call') && $this->object->hasHook('get_'.$var)) {
            return $this->object->call('get_'.$var);
        }

        // Check the for a normal style hook.
        if (method_exists($this->object, 'call') && $this->object->hasHook($var)) {
            return $this->object->call($var);
        }

        try {
            // Lastly try generic property access.
            return $this->__getDecorator()->decorate(
                is_array($this->object) ? $this->object[$var] : $this->object->$var
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getPresenterMethodFromVariable($variable)
    {
        $method = camel_case($variable);

        if (method_exists($this, $method)) {
            return $method;
        }
    }

    public function __call($method, $arguments)
    {
        if (in_array(snake_case($method), $this->protected)) {
            return null;
        }

        return parent::__call($method, $arguments);
    }

    public function __toString()
    {
        if (method_exists($this->object, '__toString')) {
            return $this->object->__toString();
        }

        return json_encode($this->object);
    }
}
