<?php

require_once __DIR__.'/Splitter/ArgumentHandler.php';
require_once __DIR__.'/Splitter/XMLHandler.php';
require_once __DIR__.'/Splitter/TestDistribution.php';

use Splitter\ArgumentHandler;
use Splitter\TestDistribution;
use Splitter\XMLHandler;

echo PHP_EOL . "version: 1.0.3" . PHP_EOL . PHP_EOL;

// Parse command-line arguments
$argumentHandler = new ArgumentHandler();

// Initialize XML handler
$junitXmlReportDir = $argumentHandler->getJUnitXmlReportDir();
$xmlHandler = new XMLHandler($junitXmlReportDir);

// Initialize test distribution
$testDistribution = new TestDistribution($argumentHandler, $xmlHandler);

// Distribute and output tests
$testDistribution->distributeTests();

exit(0);
