<?php

namespace SuperV\Platform\Support\Concerns;

trait Whenable
{
    public function if($condition)
    {
        return (new class ($this, $condition)
        {
            private $parent;

            private $condition;

            private $expecting;

            private $then;

            private $else;

            public function __construct($parent, $condition)
            {
                $this->parent = $parent;
                $this->condition = $condition;
            }

            public function then()
            {
                $this->expecting = 'then';

                return $this;
            }

            public function else()
            {
                $this->expecting = 'else';

                return $this;
            }

            public function __call($name, $arguments)
            {
                if ($this->expecting === 'then') {
                    $this->then = compact('name', 'arguments');
                } elseif ($this->expecting === 'else') {
                    $this->else = compact('name', 'arguments');

                    return $this->make();
                }

                return $this;
            }

            private function make()
            {
                if ($this->condition) {
                    call_user_func_array([$this->parent, $this->then['name']], $this->then['arguments']);
                } else {
                    call_user_func_array([$this->parent, $this->else['name']], $this->else['arguments']);
                }

                return $this->parent;
            }
        });
    }
}