<?php namespace SuperV\Platform\Support\Composer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Arrayable;

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
        //
        // How should I compose a string?
        //
        if (is_string($data)) {
            return $data;
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        //
        // I know this..
        //
        if ($data instanceof Composable) {
            $composed = $data->compose($this->params);
            return $this->compose($composed);
        }

        //
        // Collections..
        //
        if ($data instanceof Collection) {
            $data->transform(function ($item) {
                return $this->compose($item);
            });

            return $data;
        }

        //
        // Arrays or wannabes..
        //
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $value = $this->compose($value);
            }

            return $data;
        }



        //
        // It's getting harder.. Objects..
        //
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