<?php
namespace app\commands;

use app\archiver\PharArchiver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class InstallCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Installs vendors based on version stored in composer.lock.');

        $this->addWorkingDirOption();
        $this->addVendorDirOption();
        $this->addArchiveDirOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setDirectoriesFromInput($input);

        $version = $this->getVersionFromLock();
        $archive = $this->getLastArchiveByVersion($version);
        if (!$archive) {
            $output->writeln('<fg=red>Archive for version `'.$version.'` not found.</>');
            return;
        }

        $output->writeln('<fg=green>Extracting archive:'.$archive.'</>');
        $phar = new \PharData($archive);
        $phar->extractTo($this->getVendorDir(), null, true);
        $output->writeln('<fg=green>Archive extracted</>');
    }
}