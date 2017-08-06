<?php namespace SuperV\Platform\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use StringTemplate\Engine;

class Parser
{
    /** @var \Illuminate\Routing\UrlGenerator */
    protected $url;
    
    /** @var \StringTemplate\Engine */
    protected $parser;
    
    /** @var \Illuminate\Http\Request */
    protected $request;
    
    public function __construct(UrlGenerator $url, Engine $parser, Request $request)
    {
        $this->url = $url;
        $this->parser = $parser;
        $this->request = $request;
    }
    
    public function parse($target, array $data = [])
    {
        $data = $this->prepareData($data);
        
        /*
         * If the target is an array
         * then parse it recursively.
         */
        if (is_array($target)) {
            foreach ($target as $key => &$value) {
                $value = $this->parse($value, $data);
            }
        }
        
        /*
         * if the target is a string and is in a parsable
         * format then parse the target with the payload.
         */
        if (is_string($target) && str_contains($target, ['{', '}'])) {
            $target = $this->parser->render($target, $data);
        }
        
        return $target;
    }
    
    protected function prepareData(array $data)
    {
        return $this->toArray($this->mergeDefaultData($data));
    }
    
    protected function toArray(array $data)
    {
        foreach ($data as $key => &$value) {
            if (is_object($value) && $value instanceof Arrayable) {
                $value = $value->toArray();
            }
        }
        
        return $data;
    }
    
    protected function mergeDefaultData(array $data)
    {
        $url = $this->urlData();
        $request = $this->requestData();
        
        return array_merge(compact('url', 'request'), $data);
    }
    
    protected function urlData()
    {
        return [
            'previous' => $this->url->previous(),
        ];
    }
    
    protected function requestData()
    {
        $request = [
            'path'  => $this->request->path(),
            'input' => $this->request->input(),
            'uri'   => $this->request->getRequestUri(),
            'query' => $this->request->getQueryString(),
        ];
        
        if ($route = $this->request->route()) {
            $request['route'] = [
                'uri'                      => $route->uri(),
                'parameters'               => $route->parameters(),
                'parameters.to_urlencoded' => array_map(
                    function ($parameter) {
                        return urlencode($parameter);
                    },
                    $route->parameters()
                ),
                'parameter_names'          => $route->parameterNames(),
                'compiled'                 => [
                    'static_prefix'     => $route->getCompiled()->getStaticPrefix(),
                    'parameters_suffix' => str_replace(
                        $route->getCompiled()->getStaticPrefix(),
                        '',
                        $this->request->getRequestUri()
                    ),
                ],
            ];
        }
        
        return $request;
    }
}
