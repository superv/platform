<?php

namespace SuperV\Platform\Domains\Resource\Visibility;

class Condition
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Visibility\Visibility
     */
    protected $visibility;

    protected $wheres = [];

    public function __construct(Visibility $visibility)
    {
        $this->visibility = $visibility;
    }

    public function scopeIs($scope)
    {
        $this->wheres[] = ['op' => 'and', 'key' => 'scope', 'value' => $scope];

        return $this;
    }
}