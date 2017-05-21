<?php namespace Merpa\SupportModule\Compose;


use Anomaly\Streams\Platform\Entry\EntryCollection;
use Anomaly\Streams\Platform\Entry\EntryPresenter;
use Illuminate\Support\Collection;
use PhpParser\Node\Expr\ArrayItem;

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
        if ($data instanceof EntryPresenter) {
            $data = $data->getObject();
        }

        if (is_object($data) && method_exists($data, 'compose')) {
              return $this->compose($data->compose($this->params));
        }

        if ($data instanceof EntryCollection) {
            $data = $data->all();
        }

        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $value = $this->compose($value);
            }
        }

        return $data;
/*


        if (!is_object($data) || !method_exists($data, 'compose')) {
            return $data;
        }
        $composed = $composable->compose($this->params);
        if (is_array($composed)) {
            foreach ($composed as $key => &$value) {
                $value = (new Composer($this->params))->compose($value);
            }
        }

        return $composed;*/
    }
}