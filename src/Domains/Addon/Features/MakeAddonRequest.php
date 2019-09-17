<?php

namespace SuperV\Platform\Domains\Addon\Features;

class MakeAddonRequest
{
    /**
     * @var string
     */
    protected $vendor;

    /**
     * @var string
     */
    protected $package;

    /**
     * @var string
     */
    protected $addonType;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $targetPath;

    public function __construct(string $identifier, string $addonType)
    {
        list($this->vendor, $this->package) = explode('.', $identifier);
        $this->identifier = $identifier;
        $this->addonType = $addonType;
    }

    /**
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * @return string
     */
    public function getAddonType(): string
    {
        return $this->addonType;
    }

    /**
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }

    /**
     * @param string $targetPath
     */
    public function setTargetPath(?string $targetPath): void
    {
        $this->targetPath = $targetPath;
    }
}
