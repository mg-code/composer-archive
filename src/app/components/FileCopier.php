<?php

namespace app\components;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Copies vendors to vendor-mirror directory.
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class FileCopier
{
    /**
     * Copy vendors from source directory to archive directory.
     * @param $sources
     * @param $mirrorDir
     * @param array $excludes
     */
    public function copy($sources, $mirrorDir, array $excludes = [])
    {
        $sources = realpath($sources);
        $mirrorDir = realpath($mirrorDir);

        // Find files
        $files = new FileFinder($sources, $excludes);

        // Mirror directories
        $filesystem = new Filesystem();
        $filesystem->mirror($sources, $mirrorDir, $files, [
            'override' => true,
        ]);

        // Cleanup not existing
        $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($mirrorDir), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($deleteIterator as $file) {
            $origin = str_replace($mirrorDir, $sources, $file->getPathname());
            if (!$filesystem->exists($origin)) {
                $filesystem->remove($file);
            }
        }

        // Unlink .gitignore in vendor-mirror root
        if(file_exists($mirrorDir.'/.gitignore')) {
            unlink($mirrorDir.'/.gitignore');
        }
    }
}