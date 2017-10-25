<?php

namespace SuperV\Platform\Traits;

trait EnforcableTrait
{
    /** @return $this */
    public function must($otherwise = null)
    {
        return (new class ($this, $otherwise)
        {
            private $parent;

            private $otherwise;

            public function __construct($parent, $otherwise)
            {
                $this->parent = $parent;
                $this->otherwise = $otherwise;
            }

            public function __call($name, $arguments)
            {
                if (!$res = call_user_func_array([$this->parent, $name], $arguments)) {
                    $this->__fail();
                }

                return $res;
            }

            private function __fail() {
                if ($this->otherwise instanceof \Exception) {
                    throw $this->otherwise;
                }
                throw new \Exception($this->otherwise ?: 'Argument not found');
            }
        });
    }
}