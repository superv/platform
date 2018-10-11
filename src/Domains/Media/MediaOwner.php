<?php

namespace SuperV\Platform\Domains\Media;

interface MediaOwner
{
    public function mediaBag($label): MediaBag;
}