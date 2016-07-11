<?php
namespace app\commands;

use app\archiver\PharArchiver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class BuildCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds archive from current vendors state.');

        $this->addWorkingDirOption();
        $this->addVendorDirOption();
        $this->addArchiveDirOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setDirectoriesFromInput($input);

        $excludes = $this->getExcludes();

        $vendorDir = $this->getVendorDir();
        $archiveDir = $this->getArchiveDir();

        $archiver = new PharArchiver();
        $output->writeln('<fg=green>Archiving vendors..</>');
        $result = $archiver->archive($vendorDir, $archiveDir, $excludes);
        $output->writeln('<fg=green>Archive saved: '.$result.'</>');
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