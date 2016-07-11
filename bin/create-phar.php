<?php
$srcRoot = __DIR__.'/../src';
$buildRoot = __DIR__.'/../build';
$pharFile = 'vendor-mirror.phar';

$phar = new Phar($buildRoot.'/vendor-mirror.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $pharFile);
$phar->buildFromDirectory($srcRoot, '/.php$/');

$phar->setStub($phar->createDefaultStub("index.php"));