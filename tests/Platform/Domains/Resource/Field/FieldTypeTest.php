<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\FieldConfig;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Select;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TestFieldType extends \SuperV\Platform\Domains\Resource\Field\Types\FieldType
{
    protected $label = 'test-label';

    protected $type = 'test-type';

    protected $name = 'test-name';
}