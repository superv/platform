<?php namespace SuperV\Platform\Contracts;

interface Container
{
    public static function getInstance();
    
    public function make($abstract);
    
    public function makeWith($abstract, array $parameters);
}
