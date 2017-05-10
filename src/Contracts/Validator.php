<?php namespace SuperV\Platform\Contracts;

interface Validator
{
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []);
    
    public function fails();
    
    public function errors();
}
