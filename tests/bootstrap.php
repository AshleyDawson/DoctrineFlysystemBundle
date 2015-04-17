<?php

define('TESTS_TEMP_DIR', '/tmp');

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->addPsr4('AshleyDawson\\DoctrineFlysystemBundle\\Tests\\', __DIR__);