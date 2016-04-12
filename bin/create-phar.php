<?php
$srcRoot = __DIR__.'/../src';
$buildRoot = __DIR__.'/../build';
$pharFile = 'composer-archive.phar';

$phar = new Phar($buildRoot.'/composer-archive.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $pharFile);
$phar->buildFromDirectory($srcRoot, '/.php$/');

$phar->setStub($phar->createDefaultStub("index.php"));