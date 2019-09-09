<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class CreateEntryAction extends Action
{
    protected $name = 'create';

    protected $title = 'Create';

    protected $type = 'redirect';

    protected $url = 'sv/res/{res.handle}/create';

    public function onComposed(Payload $payload)
    {
        $payload->merge([
            'url'    => $this->getUrl(),
            'button' => [
                'color' => 'green',
                'size'  => 'sm',
                'title' => __('Create', ['thing' => 'User']),
            ],
        ]);
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->url;
    }
}
