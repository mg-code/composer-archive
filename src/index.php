<?php

ini_set('memory_limit', '1024M');
require(__DIR__.'/vendor/autoload.php');

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \app\commands\MirrorCommand());
$application->run();


