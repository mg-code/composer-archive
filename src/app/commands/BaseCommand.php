<?php
namespace app\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

abstract class BaseCommand extends Command
{
    protected $format = 'tar';

    private $_workingDir;
    private $_vendorDir;
    private $_archiveDir;

    /**
     * Adds working-dir option to command
     * @return void
     */
    protected function addWorkingDirOption()
    {
        $this->addOption(
            'working-dir',
            null,
            InputOption::VALUE_OPTIONAL,
            'If specified, use the given directory as working directory.',
            getcwd()
        );
    }

    /**
     * Adds vendor-dir option to command
     * @return void
     */
    protected function addVendorDirOption()
    {
        $this->addOption(
            'vendor-dir',
            null,
            InputOption::VALUE_OPTIONAL,
            'Relative path to vendors.',
            'vendor'
        );
    }

    /**
     * Adds archive-dir option to command
     * @return void
     */
    protected function addArchiveDirOption()
    {
        $this->addOption(
            'archive-dir',
            null,
            InputOption::VALUE_OPTIONAL,
            'Relative path to archive directory.',
            'vendor-archive'
        );
    }

    /**
     * Returns version hash from composer.lock
     * @return string
     * @throws \Exception
     */
    protected function getVersionFromLock()
    {
        $json = $this->readJson('composer.lock');
        if (!isset($json['hash'])) {
            throw new \Exception('Could not find hash in composer.lock file.');
        }
        return $json['hash'];
    }

    /**
     * @param $name
     * @return array
     * @throws \Exception
     */
    protected function readJson($name)
    {
        $workingDir = $this->getWorkingDir();
        $file = $workingDir.'/'.$name;

        if (!file_exists($file)) {
            throw new \Exception('File does not exists:'.$file);
        }
        $content = file_get_contents($file);
        $json = json_decode($content, true);
        if ($json === false) {
            throw new \Exception('Could not parse json file:'.$file);
        }
        return $json;
    }

    /**
     * Returns archive location, if exists
     * @return null|string
     * @throws \Exception
     */
    protected function getArchive()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->name("vendors.tar.gz")
            ->in($this->getArchiveDir());

        foreach($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            return $file->getPathname();
        }
        return null;
    }

    /**
     * Setter for working-dir
     * @param $value
     */
    protected function setWorkingDir($value)
    {
        $this->_workingDir = $value;
    }

    /**
     * Getter for working-dir
     * @return string
     * @throws \Exception
     */
    protected function getWorkingDir()
    {
        if ($this->_workingDir === null) {
            throw new \Exception('`_workingDir` is not set');
        }
        return $this->_workingDir;
    }

    /**
     * Setter for vendor-dir
     * @param $value
     */
    protected function setVendorDir($value)
    {
        $this->_vendorDir = $this->getWorkingDir().'/'.$value;
    }

    /**
     * Getter for vendor-dir
     * @return string
     * @throws \Exception
     */
    protected function getVendorDir()
    {
        if ($this->_vendorDir === null) {
            throw new \Exception('`_vendorDir` is not set');
        } else if (!file_exists($this->_vendorDir) || !is_dir($this->_vendorDir)) {
            throw new \Exception('vendor-dir does not exists:'.$this->_vendorDir);
        }

        return $this->_vendorDir;
    }

    /**
     * Setter for archive-dir
     * @param $value
     */
    protected function setArchiveDir($value)
    {
        $this->_archiveDir = $this->getWorkingDir().'/'.$value;
    }

    /**
     * Getter for archive-dir
     * @return string
     * @throws \Exception
     */
    protected function getArchiveDir()
    {
        if ($this->_archiveDir === null) {
            throw new \Exception('`_archiveDir` is not set');
        } else if (!file_exists($this->_archiveDir) || !is_dir($this->_archiveDir)) {
            throw new \Exception('archive-dir does not exists:'.$this->_archiveDir);
        }
        return $this->_archiveDir;
    }

    /**
     * Sets directories from input
     * @param InputInterface $input
     */
    protected function setDirectoriesFromInput(InputInterface $input)
    {
        $workingDir = $input->getOption('working-dir');
        $vendorDir = $input->getOption('vendor-dir');
        $archiveDir = $input->getOption('archive-dir');

        $this->setWorkingDir($workingDir);
        $this->setVendorDir($vendorDir);
        $this->setArchiveDir($archiveDir);
    }
}