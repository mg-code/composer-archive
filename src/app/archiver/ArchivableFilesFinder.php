<?php

namespace app\archiver;

use FilesystemIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * A Symfony Finder wrapper which locates files that should go into archives
 * This class is taken from composer and customized.
 * This class does not handle .gitignore, .gitattribute and .hgignore files. Otherwise all files located in vendors directory are ignored.
 * @author Maris Graudins <maris@mg-interactive.lv>
 * @author Nils Adermann <naderman@naderman.de>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ArchivableFilesFinder extends \FilterIterator
{
    /**
     * @var Finder
     */
    protected $finder;

    /**
     * Initializes the internal Symfony Finder with appropriate filters
     * @param string $sources Path to source files to be archived
     * @param array $excludes Composer's own exclude rules from composer.json
     */
    public function __construct($sources, array $excludes)
    {
        $sources = static::normalizePath($sources);

        $filters = [
            new ComposerExcludeFilter($sources, $excludes),
        ];

        $this->finder = new Finder();

        $filter = function (\SplFileInfo $file) use ($sources, $filters) {
            if ($file->isLink() && strpos($file->getLinkTarget(), $sources) !== 0) {
                return false;
            }

            $relativePath = preg_replace(
                '#^'.preg_quote($sources, '#').'#',
                '',
                static::normalizePath($file->getRealPath())
            );

            $exclude = false;
            foreach ($filters as $filter) {
                $exclude = $filter->filter($relativePath, $exclude);
            }

            return !$exclude;
        };

        if (method_exists($filter, 'bindTo')) {
            $filter = $filter->bindTo(null);
        }

        $this->finder
            ->in($sources)
            ->filter($filter)
            ->ignoreVCS(true)
            ->ignoreDotFiles(false);

        parent::__construct($this->finder->getIterator());
    }

    public function accept()
    {
        /** @var SplFileInfo $current */
        $current = $this->getInnerIterator()->current();

        if (!$current->isDir()) {
            return true;
        }

        $iterator = new FilesystemIterator($current, FilesystemIterator::SKIP_DOTS);
        return !$iterator->valid();
    }

    /**
     * Normalize a path. This replaces backslashes with slashes, removes ending
     * slash and collapses redundant separators and up-level references.
     * @param  string $path Path to the file or directory
     * @return string
     */
    public static function normalizePath($path)
    {
        $parts = [];
        $path = strtr($path, '\\', '/');
        $prefix = '';
        $absolute = false;

        if (preg_match('{^([0-9a-z]+:(?://(?:[a-z]:)?)?)}i', $path, $match)) {
            $prefix = $match[1];
            $path = substr($path, strlen($prefix));
        }

        if (substr($path, 0, 1) === '/') {
            $absolute = true;
            $path = substr($path, 1);
        }

        $up = false;
        foreach (explode('/', $path) as $chunk) {
            if ('..' === $chunk && ($absolute || $up)) {
                array_pop($parts);
                $up = !(empty($parts) || '..' === end($parts));
            } elseif ('.' !== $chunk && '' !== $chunk) {
                $parts[] = $chunk;
                $up = '..' !== $chunk;
            }
        }

        return $prefix.($absolute ? '/' : '').implode('/', $parts);
    }
}
