<?php

define('TESTS_TEMP_DIR', '/tmp');

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->addPsr4('AshleyDawson\\DoctrineFlysystemBundle\\Tests\\', __DIR__);

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$reader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());

$_ENV['annotation_reader'] = $reader;