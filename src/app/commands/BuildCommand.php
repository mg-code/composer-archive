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
        $version = $this->getVersionFromLock();

        // Check if archive exists, if exists asks a question.
        if($this->getLastArchiveByVersion($version)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Archive already exists, create a new one? [y|n]:', false);
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $vendorDir = $this->getVendorDir();
        $archiveDir = $this->getArchiveDir();

        $archiver = new PharArchiver();
        $output->writeln('<fg=green>Archiving vendors..</>');
        $result = $archiver->archive($vendorDir, $archiveDir, $version, $excludes);
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