<?php

namespace SuperV\Platform\Domains\UI\Table;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Support\Collection;

class Table
{
    /** @var array */
    protected $data;

    /** @var EntryModel */
    protected $model;

    protected $entries;

    protected $columns = [];

    protected $rows;

    protected $buttons = [];

    protected $actions;

    protected $options;

    public function __construct(
        Collection $entries,
        RowCollection $rows,
        Collection $data,
        Collection $options
    ) {
        $this->entries = $entries;
        $this->data = $data;
        $this->options = $options;
        $this->rows = $rows;
    }

    public function addButton(array $params)
    {
        $this->buttons[] = $params;
    }

    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function setEntries($entries)
    {
        $this->entries = $entries;

        return $this;
    }

    /**
     * @return EntryCollection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @return mixed
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data->put($key, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function setOptions(Collection $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options->put($key, $value);

        return $this;
    }

    /**
     * @param        $key
     * @param  null  $default
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->options->get($key, $default);
    }

    public function addRow(Row $row)
    {
        $this->rows->push($row);

        return $this;
    }

    /**
     * Set the table rows.
     *
     * @param  RowCollection $rows
     *
     * @return $this
     */
    public function setRows(RowCollection $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the table rows.
     *
     * @return RowCollection
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return EntryModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param EntryModel $model
     *
     * @return Table
     */
    public function setModel($model): Table
    {
        $this->model = $model;

        return $this;
    }

    public function toBlock()
    {
        return [
            'component' => 'sv-table',
            'props'     => [
                'columns' => $this->getColumns(),
                'rows'    => $this->getRows(),
            ],
        ];
    }
}
