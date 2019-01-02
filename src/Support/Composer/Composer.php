<?php namespace SuperV\Platform\Support\Composer;

use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Composer
{
    /**
     * @var \SuperV\Platform\Support\Composer\Tokens
     */
    protected $tokens;

    public function __construct($tokens = [])
    {
        $this->tokens = $tokens instanceof Tokens ? $tokens : new Tokens($tokens);
    }

    public function compose($data)
    {
        $composed = $this->__compose($data);
        if ($tokens = $this->tokens->get()) {
            $composed = sv_parse($composed, $this->tokens->get());
        }

        return $composed;
    }

    public function __compose($data)
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
            $composed = $data->compose($this->tokens);

            return $this->compose($composed);
        }

        //
        // Collections..
        //
        if ($data instanceof Collection) {
            return $data->map(function ($item) {
                return $this->compose($item);
            });

//            $data->transform(function ($item) {
//                return $this->compose($item);
//            });
//
//            return $data;
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
                if (array_key_exists(self::class, class_implements($class))) {
                    return $this->compose((new $class($data))->compose($this->tokens));
                }
            }

            /**
             * Search  nearby composers of the Interface
             */
//            if ($interfaces = class_implements($data)) {
//                foreach ($interfaces as $interface) {
//                    if (class_exists($class = $interface.'Composer')) {
//                        return $this->compose((new $class($data))->compose($this->params));
//                    }
//                }
//            }

            /**
             * Search in Port Composers
             */
            if ($port = \Platform::port()) {
                if ($composersNamespace = $port->getComposers()) {
                    $shortClassName = $this->getShortClassName($data);
                    $composerClass = $composersNamespace."\\".$shortClassName."Composer";
                    if (class_exists($composerClass)) {
                        return $this->compose((new $composerClass($data))->compose($this->tokens));
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function getShortClassName($data)
    {
        if ($data instanceof ResourceEntry) {
            return str_replace(' ', '', $data->getResource()->getSingularLabel());
        }
        $parts = explode("\\", get_class($data));
        $shortClassName = end($parts);

        return $shortClassName;
    }
}