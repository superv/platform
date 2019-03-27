<?php

namespace SuperV\Platform\Domains\UI\Nucleo;

use Illuminate\Database\Eloquent\Builder;

class ChartData
{
    /**
     * @var Builder
     */
    protected $query;

    protected $labels = [];

    protected $data = [];

    protected $label = 'Labels';

    protected $backgroundColor = 'rgba(13,88,145, 0.2)';

    protected $borderColor = 'rgba(13,88,145,1)';

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

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
                    'strokeColor' => 'rgba(132,88,145,1)',
                    'borderColor'     => $this->borderColor,
                    'borderWidth'     => 2,
                ],
            ],
        ];
    }

    public function byCustom($group, $callback = null)
    {
        if (! is_array($group)) {
            $titleColumn = $valueColumn = $group;
        } else {
            $titleColumn = $group['title'];
            $valueColumn = $group['value'];
        }
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->newQuery();
//        $query->where('created_at', '>=', \Carbon\Carbon::now()->subMonth());
        $query
            ->select([
                \DB::raw('COUNT(*) as "count"'),
                $valueColumn,
            ]);

        if ($callback instanceof \Closure) {
            $callback($query);
        }

        $dataset = $query->groupBy($valueColumn)
                         ->get()
                         ->pluck('count', $titleColumn)
                         ->all();

        $this->data = array_values($dataset);
        $this->labels = array_keys($dataset);

        $this->backgroundColor = array_slice($this->color(), 0, count($this->data));
        $this->borderColor = '#FFFFFF';
    }

    public function rand_color()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function byDate()
    {
        $query = $this->newQuery();
        $dataset = $query->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as "count"')
                         ->where('created_at', '>=', \Carbon\Carbon::now()->subMonth())
                         ->groupBy('date')
                         ->orderBy('date', 'ASC')
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

    protected function newQuery(): Builder
    {
        return $this->query;
    }
}