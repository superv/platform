<?php namespace SuperV\Platform\Support\Composer;

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
//        throw new \Exception('I dont know how to compose this');
    }

    protected function old($data)
    {
        if ($data instanceof Composable) {
            $class = get_class($data);
            if (method_exists($data, 'compose')) {
                return $this->compose($data->compose($this->params));
            } else {
                $composerClass = $class.'Composer';
                if (class_exists($composerClass)) {
                    return $this->compose((new $composerClass($data))->compose($this->params));
                } else {
                    if ($port = \Platform::port()) {
                        if ($composersNamespace = $port->getComposers()) {
                            $parts = explode("\\", $class);
                            $shortClassName = end($parts);
                            $composerClass = $composersNamespace."\\".$shortClassName."Composer";
                            if (class_exists($composerClass)) {
                                return $this->compose((new $composerClass($data))->compose($this->params));
                            }
                        }
                    }
                }
            }
        }
    }
}