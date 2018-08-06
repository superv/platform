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
                            $composerClass = $composersNamespace . "\\".$shortClassName . "Composer";
                            if (class_exists($composerClass)) {
                                return $this->compose((new $composerClass($data))->compose($this->params));
                            }
                        }
                    }
                }
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $value = $this->compose($value);
            }
        }

        return $data;
    }
}