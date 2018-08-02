<?php

namespace SuperV\Platform\Domains\Nucleo\Resource;

class ResourceEditor
{
    protected $cols = 1;

    protected $rows;

    protected $fields = [];

    protected $matrix;

    protected $model;

    protected $rowCount = 1;

    /**
     * @var \SuperV\Platform\Domains\Nucleo\Resource\Resource
     */
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function render()
    {
        $this->make();

        return [
            'data' => [
                'fields' => $this->fields,
                'config' => [
                    'cols' => $this->cols,
                    'rows' => 3,
                ],
            ],
        ];
    }

    protected function make()
    {
        foreach ($this->resource->fields() as $key => $field) {
            array_set($field, 'location', $this->getLocation($key));
            $this->fields[$key] = $field;
        }

        return $this;
    }

    protected function getLocation($slug)
    {
        if (! $this->matrix) {
            return [
                'col' => 1,
                'row' => $this->rowCount++,
            ];
        }

        foreach ($this->matrix as $rowIndex => $row) {
            $colIndex = 1;
            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $colSpan = $cell[1];
                    if ($cell[0] === $slug) {
                        return ['col' => $colIndex, 'row' => $rowIndex + 1, 'col-span' => $colSpan];
                    }
                    $colIndex += $colSpan - 1;
                }
                if (! is_array($cell) && $cell === $slug) {
                    return ['col' => $colIndex, 'row' => $rowIndex + 1];
                }
                $colIndex++;
                if ($colIndex > $this->cols) {
                    $this->cols = $colIndex - 1;
                }
            }
        }
    }
}