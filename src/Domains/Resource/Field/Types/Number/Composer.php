<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Number;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Composer as BaseComposer;

class Composer extends BaseComposer
{
    public function view(EntryContract $entry): void
    {
        if ($this->getConfigValue('type') === 'decimal') {
            $value = (float)number_format(
                $this->payload->get('value'),
                $this->getConfigValue('places'),
                $this->getConfigValue('dec_point', '.'),
                $this->getConfigValue('thousands_sep', '')
            );
        } else {
            $value = (int)$this->payload->get('value');
        }

        $this->payload->set('value', $value);
    }
}