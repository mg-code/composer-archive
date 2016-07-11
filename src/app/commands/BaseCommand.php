<?php
namespace app\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

abstract class BaseCommand extends Command
{
    private $_workingDir;
    private $_vendorDir;
    private $_mirrorDir;

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
     * Adds mirror-dir option to command
     * @return void
     */
    protected function addMirrorDirOption()
    {
        $this->addOption(
            'mirror-dir',
            null,
            InputOption::VALUE_OPTIONAL,
            'Relative path to mirror directory.',
            'vendor-mirror'
        );
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
     * Setter for mirror-dir
     * @param $value
     */
    protected function setMirrorDir($value)
    {
        $this->_mirrorDir = $this->getWorkingDir().'/'.$value;
    }

    /**
     * Getter for mirror-dir
     * @return string
     * @throws \Exception
     */
    protected function getMirrorDir()
    {
        if ($this->_mirrorDir === null) {
            throw new \Exception('`_mirrorDir` is not set');
        } else if (!file_exists($this->_mirrorDir) || !is_dir($this->_mirrorDir)) {
            throw new \Exception('mirror-dir does not exists:'.$this->_mirrorDir);
        }
        return $this->_mirrorDir;
    }

    /**
     * Sets directories from input
     * @param InputInterface $input
     */
    protected function setDirectoriesFromInput(InputInterface $input)
    {
        $workingDir = $input->getOption('working-dir');
        $vendorDir = $input->getOption('vendor-dir');
        $mirrorDir = $input->getOption('mirror-dir');

        $this->setWorkingDir($workingDir);
        $this->setVendorDir($vendorDir);
        $this->setMirrorDir($mirrorDir);
    }
}