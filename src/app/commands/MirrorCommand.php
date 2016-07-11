<?php
namespace app\commands;

use app\components\FileCopier;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MirrorCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('mirror')
            ->setDescription('Mirrors vendors from current vendors state.');

        $this->addWorkingDirOption();
        $this->addVendorDirOption();
        $this->addMirrorDirOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setDirectoriesFromInput($input);

        $excludes = $this->getExcludes();

        $vendorDir = $this->getVendorDir();
        $mirrorDir = $this->getMirrorDir();

        $copier = new FileCopier();
        $output->writeln('<fg=green>Mirroring vendors..</>');
        $copier->copy($vendorDir, $mirrorDir, $excludes);
        $output->writeln('<fg=green>Mirroring finished</>');
    }

    /**
     * Returns list of patterns for excluded paths.
     * @return array
     * @throws \Exception
     */
    protected function getExcludes()
    {
        $json = $this->readJson('composer.json');
        return isset($json['archive']['exclude']) ? $json['archive']['exclude'] : [];
    }
}