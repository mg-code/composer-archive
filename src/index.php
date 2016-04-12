<?php

require(__DIR__.'/vendor/autoload.php');

use Symfony\Component\Console\Application;
use app\commands\BuildCommand;
use app\commands\InstallCommand;

$application = new Application();

$application->add(new BuildCommand());
$application->add(new InstallCommand());
$application->run();


