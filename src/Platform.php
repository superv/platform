<?php

namespace SuperV\Platform;

use SuperV\Modules\Supreme\Domains\Drop\Drop;
use SuperV\Modules\Supreme\Domains\Server\Model\ServerManifest;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\Module\Module;
use SuperV\Platform\Domains\Task\TaskManifest;

class Platform extends Module
{
    protected $title = 'SuperV';

       protected $link = '/superv';

       protected $icon = 'check';

       protected $navigation = true;

       protected $manifests = [
//           TaskManifest::class
          ];
}
