<?php

namespace Splitter;

use DOMDocument;

class TestDistribution
{
    private ArgumentHandler $argumentHandler;
    private XMLHandler $xmlHandler;

    public function __construct(ArgumentHandler $argumentHandler, XMLHandler $xmlHandler)
    {
        $this->argumentHandler = $argumentHandler;
        $this->xmlHandler = $xmlHandler;
    }

    public function distributeTests(): void
    {
        $testFileResults = $this->xmlHandler->getTestFileResults();
        $this->addNewTests($testFileResults);
        $nodeTotal = $this->argumentHandler->getNodeTotal();
        $nodeIndex = $this->argumentHandler->getNodeIndex();

        $nodes = $this->initializeNodes($nodeTotal);
        $this->assignTestsToNodes($testFileResults, $nodes);

        if (is_null($nodeIndex)) {
            foreach ($nodes as $index => $node) {
                $this->createNodePartialFile($nodes, $index);
            }
        } else {
            $this->createNodePartialFile($nodes, $nodeIndex);
        }
    }

    private function addNewTests(array &$testFileResults): void
    {
        $unitTests = $this->getTestFiles('Unit');
        $featureTests = $this->getTestFiles('Feature');

        foreach (array_merge($unitTests, $featureTests) as $testFile) {
            if (!isset($testFileResults[$testFile])) {
                $testFileResults[$testFile] = 1;
            }
        }
    }

    private function getTestFiles(string $directoryName): array
    {
        $directory = new \RecursiveDirectoryIterator('tests/' . $directoryName);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];

        foreach ($iterator as $info) {
            if ($info->isFile()) {
                $files[] = $info->getRealPath();
            }
        }

        return $files;
    }

    private function initializeNodes(int $nodeTotal): array
    {
        $nodes = [];
        for ($i = 0; $i < $nodeTotal; $i++) {
            $nodes[] = [
                'test_files' => [],
                'recorded_total_time' => 0.0
            ];
        }
        return $nodes;
    }

    private function assignTestsToNodes(array $testFileResults, array &$nodes): void
    {
        foreach ($testFileResults as $filePath => $time) {
            $index = $this->findNodeWithLeastTime($nodes);
            $nodes[$index]['test_files'][] = $filePath;
            $nodes[$index]['recorded_total_time'] += $time;
        }
    }

    private function findNodeWithLeastTime(array $nodes): int
    {
        $index = 0;
        $minTime = PHP_INT_MAX;
        foreach ($nodes as $i => $node) {
            if ($node['recorded_total_time'] < $minTime) {
                $minTime = $node['recorded_total_time'];
                $index = $i;
            }
        }
        return $index;
    }

    private function createNodePartialFile(array $nodes, int $nodeIndex): void
    {
        // load current phpunit.xml file to add the test files to it
        // to apply any new updates made in the original phpunit.xml file into the new partial file
        $xml = new DOMDocument();
        $xml->load('phpunit.xml');

        $testsuite = $xml->createElement('testsuite');
        $testsuite->setAttribute('name', 'partial');

        $debug = $this->argumentHandler->hasDebugFlag();

        if ($debug) {
            echo '[DEBUG] Node index: ' . $nodeIndex . PHP_EOL;
        }

        foreach ($nodes[$nodeIndex]['test_files'] as $index => $file) {
            $testsuite->appendChild($xml->createElement('file', $file));

            if ($debug) {
                echo "[DEBUG] $index -> $file" . PHP_EOL;
            }
        }

        if ($debug) {
            echo '[DEBUG] Total test files: ' . count($nodes[$nodeIndex]['test_files']) . PHP_EOL;
            echo '[DEBUG] Total recorded time: ' . $nodes[$nodeIndex]['recorded_total_time'] . PHP_EOL;
        }

        $testsuites = $xml->createElement('testsuites');
        $testsuites->appendChild($testsuite);

        // replace the original "testsuites" and place the new partial one
        $phpunit = $xml->getElementsByTagName('phpunit')->item(0);

        $bootstrap = $phpunit->getAttribute('bootstrap');
        $phpunit->setAttribute('bootstrap', $bootstrap);

        $phpunit->replaceChild($testsuites, $phpunit->getElementsByTagName('testsuites')->item(0));

        $xmlPartialDir = $this->argumentHandler->getXmlPartialDir();

        $xml->save($xmlPartialDir . "/phpunit-partial-$nodeIndex.xml");
    }
}
