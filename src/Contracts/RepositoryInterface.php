<?php namespace SuperV\Platform\Contracts;

interface RepositoryInterface
{
    public function find($id);
    
    public function findOrFail($id);
    
    public function delete($id);
    
    public function all();
    
    public function save($entry);
}
