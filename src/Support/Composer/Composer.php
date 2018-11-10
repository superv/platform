<?php namespace SuperV\Platform\Support\Composer;

use Illuminate\Support\Collection;

class Composer
{
    /**
     * @var array
     */
    private $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function compose($data)
    {
        if (is_string($data)) {
            return $data;
        }
        if ($data instanceof Composable) {
            return $this->compose($data->compose($this->params));
        }

        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $value = $this->compose($value);
            }

            return $data;
        }

        if ($data instanceof Collection || $data instanceof \Illuminate\Support\Collection) {
            $data->transform(function ($item) {
                return $this->compose($item);
            });

            return $data;
        }

        if (is_object($data)) {
            /**
             * Search  nearby composers of the Class
             */
            if (class_exists($class = get_class($data).'Composer')) {
                return $this->compose((new $class($data))->compose($this->params));
            }

            /**
             * Search  nearby composers of the Interface
             */
            if ($interfaces = class_implements($data)) {
                foreach ($interfaces as $interface) {
                    if (class_exists($class = $interface.'Composer')) {
                        return $this->compose((new $class($data))->compose($this->params));
                    }
                }
            }

            /**
             * Search in Port Composers
             */
            if ($port = \Platform::port()) {
                if ($composersNamespace = $port->getComposers()) {
                    $parts = explode("\\", get_class($data));
                    $shortClassName = end($parts);
                    $composerClass = $composersNamespace."\\".$shortClassName."Composer";
                    if (class_exists($composerClass)) {
                        return $this->compose((new $composerClass($data))->compose($this->params));
                    }
                }
            }
        }

        return $data;
    }
}