<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;

class Blueprint extends FieldBlueprint
{
    /**
     * @var string
     */
    protected $related;

    /**
     * @var string
     */
    protected $localKey;

    /**
     * @var string
     */
    protected $remoteKey;

    public function related($related)
    {
        $this->related = $related;

        return $this;
    }

    public function mergeConfig(): array
    {
        return [
            'related'    => $this->related,
            'local_key'  => $this->getLocalKey(),
            'remote_key' => $this->getRemoteKey(),
        ];
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function withLocalKey(string $localKey): Blueprint
    {
        $this->localKey = $localKey;

        return $this;
    }

    public function getLocalKey(): ?string
    {
        return $this->localKey;
    }

    public function withRemoteKey(string $remoteKey): Blueprint
    {
        $this->remoteKey = $remoteKey;

        return $this;
    }

    public function getRemoteKey(): ?string
    {
        return $this->remoteKey;
    }
}