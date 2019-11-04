<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Support\Parser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Skeleton
{
    protected $sourcePath;

    protected $targetBase;

    protected $contentTokens;

    protected $filenameTokens;

    /**
     * @var \SuperV\Platform\Contracts\Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function from($sourcePath)
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function withTokens($contentTokens, $filenameTokens)
    {
        $this->contentTokens = $contentTokens;
        $this->filenameTokens = $filenameTokens;

        return $this;
    }

    public function copyTo($targetBase)
    {
        $this->targetBase = $targetBase;

        $this->copy();
    }

    public function copy()
    {
        /** @var SplFileInfo $file */
        foreach ((new Finder)->ignoreDotFiles(false)->in($this->sourcePath)->files() as $file) {
            $targetPath = $this->targetBase.'/'.$file->getRelativePath();
            if (! $this->filesystem->exists($targetPath)) {
                $this->filesystem->makeDirectory($targetPath, 0755, true, true);
            }
            $targetFilename = str_replace(array_keys($this->filenameTokens), array_values($this->filenameTokens), $file->getBasename());

            $content = file_get_contents($file->getPathname());
            $stubbed = app(Parser::class)->parse($content, $this->contentTokens);
            file_put_contents($targetPath.'/'.str_replace_last('.stub', '', $targetFilename), $stubbed);
        }
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}