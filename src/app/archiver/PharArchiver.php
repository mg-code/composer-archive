<?php

namespace app\archiver;

/**
 * This class is taken from composer and customized.
 * @author Maris Graudins <maris@mg-interactive.lv>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Till Klampaeckel <till@php.net>
 * @author Nils Adermann <naderman@naderman.de>
 * @author Matthieu Moquet <matthieu@moquet.net>
 */
class PharArchiver
{
    public function archive($sources, $archiveDir, $version, array $excludes = [])
    {
        $sources = realpath($sources);
        $time = time();
        $target = "{$archiveDir}/{$time}-{$version}.tar";

        $phar = new \PharData($target, null, null, \Phar::TAR);
        $files = new ArchivableFilesFinder($sources, $excludes);
        $phar->buildFromIterator($files, $sources);

        // Check can be compressed?
        if (!$phar->canCompress(\Phar::GZ)) {
            throw new \RuntimeException(sprintf('Can not compress to %s format', 'tar.gz'));
        }

        // Compress the new tar
        $phar->compress(\Phar::GZ, '.tar.gz');
        // Make the correct filename

        // destroy phar instance and delete original file
        unset($phar);
        unlink($target);

        return $target.'.gz';
    }
}