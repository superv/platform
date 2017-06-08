<?php namespace SuperV\Platform\Domains\Model;

interface RepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $attributes);

    public function update(array $attributes = []);

    public function withSlug($slug);
}