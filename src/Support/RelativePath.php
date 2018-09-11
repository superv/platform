<?php

namespace SuperV\Platform\Support;

use InvalidArgumentException;

class RelativePath
{
    protected $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function get($path)
    {
        if (substr($this->basePath, 0, 1) !== '/') {
            throw new InvalidArgumentException('Base path must be a real path');
        }

        if (substr($path, 0, 1) !== '/') {
            return $path;
        }

        $baseParts = explode('/', $this->basePath);
        $pathParts = explode('/', $path);
        $same = array_intersect($baseParts, $pathParts);

        $samePartsCount = count($same);
        $pathPartsCount = count($pathParts);
        $basePartsCount = count($baseParts);

        if ($baseParts === $pathParts) {
            return './';
        }

        if ($samePartsCount === $basePartsCount) {
            $remainingOfPath = array_slice($pathParts, $basePartsCount, $pathPartsCount - $basePartsCount);

            return implode('/', $remainingOfPath);
        }

        $upToCommonParent = str_repeat('../', $basePartsCount - $samePartsCount);
        $remainingOfPath = array_slice($pathParts, $samePartsCount, $pathPartsCount - $samePartsCount);

        return $upToCommonParent.implode('/', $remainingOfPath);
//        dd($upToCommonParent, implode('/', $same), $baseParts, $pathParts);
    }
}