<?php

namespace SuperV\Platform\Domains\UI;

use Lakcom\Modules\Core\Domains\Client;

class ChartData
{
    /** @var string */
    protected $model = Client::class;

    protected $labels = [];

    protected $data = [];

    protected $label = 'Clients';

    protected $backgroundColor = 'rgba(13,88,145, 0.2)';

    protected $borderColor = 'rgba(13,88,145,1)';

    public function get($group, $callback = null)
    {
        if ($group === 'date') {
            $this->byDate();
        } else {
            $this->byCustom($group, $callback);
        }

        return [
            'labels'   => $this->labels,
            'datasets' => [
                [
                    'label'           => $this->label,
                    'data'            => $this->data,
                    'backgroundColor' => $this->backgroundColor,
                    'borderColor'     => $this->borderColor,
                    'borderWidth'     => 2,
                ],
            ],
        ];
    }

    public function byCustom($column, $callback = null)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->model::query();
//        $query->where('created_at', '>=', \Carbon\Carbon::now()->subMonth());
        $query
            ->select([
                \DB::raw('COUNT(*) as "count"'),
                $column,
            ]);

        if ($callback instanceof \Closure) {
            $callback($query);
        } else {

        }

        $dataset = $query->groupBy($column)
                         ->get()
                         ->pluck('count', $column)
                         ->all();

        $this->data = array_values($dataset);
        $this->labels = array_keys($dataset);

//        $this->backgroundColor = array_map(function ($data) {
//            return $this->rand_color();
//        }, $this->data);

        $this->backgroundColor = array_slice($this->color(), 0, count($this->data));
        $this->borderColor = '#FFFFFF';
    }

    public function rand_color()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function byDate()
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->model::query();
        $dataset = $query->where('created_at', '>=', \Carbon\Carbon::now()->subMonth())
                         ->groupBy('date')
                         ->orderBy('date', 'ASC')
                         ->get([
                             \DB::raw('DATE(created_at) as date'),
                             \DB::raw('COUNT(*) as "count"'),
                         ])
                         ->pluck('count', 'date')
                         ->all();

        $allFoundDates = array_keys($dataset);
        $period = new \DatePeriod(
            new \DateTime(head($allFoundDates)),
            new \DateInterval('P1D'),
            new \DateTime(last($allFoundDates))
        );

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            if (! array_has($dataset, $formatted)) {
                array_set($dataset, $formatted, 0);
            }
        }
        ksort($dataset);

        $this->data = array_values($dataset);
        $this->labels = array_keys($dataset);
    }

    public function color()
    {
        return ['#3366CC',
            '#DC3912',
            '#FF9900',
            '#109618',
            '#990099',
            '#3B3EAC',
            '#0099C6',
            '#DD4477',
            '#66AA00',
            '#B82E2E',
            '#316395',
            '#994499',
            '#22AA99',
            '#AAAA11',
            '#6633CC',
            '#E67300',
            '#8B0707',
            '#329262',
            '#5574A6',
            '#3B3EAC',];
    }
}