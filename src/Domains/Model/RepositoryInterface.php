<?php namespace SuperV\Platform\Domains\Model;

interface RepositoryInterface
{
    public function all();

    public function find($id);

    public function findAll(array $ids);

    public function create(array $attributes);

    public function newQuery();

    public function newInstance(array $attributes = []);

    public function count();

    public function update(array $attributes = []);

    public function withSlug($slug);

    public function enabled();

    public function collection($items);
}