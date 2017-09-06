<?php namespace SuperV\Platform\Domains\Setting;

class JSON  implements \JsonSerializable
{
    protected $data;

    /**
     * @var
     */
    private $src;

    /**
     * JSON constructor
     *
     * @param      $src
     * @param null $trg
     */
    public function __construct($src)
    {
        $this->data = [];
        $this->load();
        $this->src = $src;
    }

    public function load()
    {
        if (file_exists($this->src)) {
            $text = file_get_contents($this->src);
            $this->data = json_decode($text, JSON_OBJECT_AS_ARRAY);
            $error = json_last_error();

            if ($error !== JSON_ERROR_NONE) {
                switch ($error) {
                    case JSON_ERROR_DEPTH:
                        $error = 'Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $error = 'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $error = 'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $error = 'Malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $error = 'Unknown error';
                        break;
                }
                throw new \Exception("$error in '{$this->src}'");
            }
        }

        return $this;
    }

    public function __get($name)
    {
        if ($name === 'data') {
            return $this->data;
        }
    }

    public function set($key, $value = null)
    {
        if (is_string($key)) {
            array_set($this->data, $key, $value);
        }

        return $this;
    }

    public function get($key = '', $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    public function has($key)
    {
        return array_has($this->data, $key);
    }

    public function create()
    {
        $text = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->_save($this->src, $text);
    }

    function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * Saves content to a file, updating file permissions to ensure a save
     *
     * @param   string $file
     * @param   string $data
     */
    protected function _save($file, $data)
    {
        // variables
        $folder = dirname($file);

        // ensure folder exists
        if (! file_exists($folder)) {
            mkdir($folder, 0777, true);
        } // ensure folder is writable
        elseif (! is_writable($folder)) {
            chmod($folder, 0777);
        }

        // write file
        if (file_exists($file) && ! is_writable($file)) {
            chmod($file, 0644);
        }
        file_put_contents($file, $data);
    }
}
