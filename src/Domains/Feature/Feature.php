<?php namespace SuperV\Platform\Domains\Feature;

use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Feature
{
    use MarshalTrait;
    use DispatchesJobs;
    use JobDispatcherTrait;
}