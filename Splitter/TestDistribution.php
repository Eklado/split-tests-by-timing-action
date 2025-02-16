<?php

namespace Splitter;

class TestDistribution
{
    private ArgumentHandler $argumentHandler;
    private XMLHandler $xmlHandler;
    private string $routePrefix;
    private bool $debugEnabled;

    public function __construct (ArgumentHandler $argumentHandler, XMLHandler $xmlHandler)
    {
        $this->argumentHandler = $argumentHandler;
        $this->xmlHandler = $xmlHandler;
        $this->routePrefix = $argumentHandler->getBasePath() . '/';
        $this->debugEnabled = $argumentHandler->hasDebugFlag();
    }

    public function distributeTests (): void
    {
        $testFileResults = $this->xmlHandler->getTestFileResults();
        $fileResultsCount = count($testFileResults);

        echo "[INFO] Total fetched file results: $fileResultsCount files" . PHP_EOL . PHP_EOL;

        if ($this->debugEnabled && $fileResultsCount > 0) {
            foreach ($testFileResults as $file => $time) {
                echo "[DEBUG] $file -> took ($time) s" . PHP_EOL;
            }

            echo PHP_EOL . "------------------" . PHP_EOL . PHP_EOL;
        }

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

    private function addNewTests (array &$testFileResults): void
    {
        foreach ($this->argumentHandler->getTestDirectories() as $testDirectory) {
            foreach ($this->getTestFiles($testDirectory) as $testFile) {
                if (!isset($testFileResults[$testFile])) {
                    $testFileResults[$testFile] = 1;
                }
            }
        }
    }

    private function getTestFiles (string $directoryName): array
    {
        $directory = new \RecursiveDirectoryIterator($this->routePrefix . $directoryName);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];

        foreach ($iterator as $info) {
            if ($info->isFile() && $info->getExtension() === 'php') {
                $files[] = $info->getRealPath();
            }
        }

        return $files;
    }

    private function initializeNodes (int $nodeTotal): array
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

    private function assignTestsToNodes (array $testFileResults, array &$nodes): void
    {
        foreach ($testFileResults as $filePath => $time) {
            $index = $this->findNodeWithLeastTime($nodes);
            $nodes[$index]['test_files'][] = $filePath;
            $nodes[$index]['recorded_total_time'] += $time;
        }
    }

    private function findNodeWithLeastTime (array $nodes): int
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

    private function createNodePartialFile (array $nodes, int $nodeIndex): void
    {
        // load current phpunit.xml file to add the test files to it
        // to apply any new updates made in the original phpunit.xml file into the new partial file
        $xml = simplexml_load_file($this->routePrefix . 'phpunit.xml');

        unset($xml->testsuites);
        $testsuites = $xml->addChild('testsuites');
        $testsuite = $testsuites->addChild('testsuite');
        $testsuite->addAttribute('name', 'partial');

        if ($this->debugEnabled) {
            echo '[DEBUG] Node index: ' . $nodeIndex . PHP_EOL;
        }

        foreach ($nodes[$nodeIndex]['test_files'] as $index => $file) {
            $testsuite->addChild('file', $file);

            if ($this->debugEnabled) {
                echo "[DEBUG] $index -> $file" . PHP_EOL;
            }
        }

        if ($this->debugEnabled) {
            echo '[DEBUG] Total test files: ' . count($nodes[$nodeIndex]['test_files']) . PHP_EOL;
            echo '[DEBUG] Total recorded time: ' . $nodes[$nodeIndex]['recorded_total_time'] . PHP_EOL;
        }

        $this->convertRelativeToAbsolutePaths($xml);

        // Save the updated XML to a new file
        $xmlPartialDir = $this->argumentHandler->getXmlPartialDir();
        $xml->asXML($xmlPartialDir . "/phpunit-partial-$nodeIndex.xml");
    }

    /**
     * Define a function to convert relative paths to absolute paths
     *
     * @param $element
     *
     * @return void
     */
    function convertRelativeToAbsolutePaths ($element): void
    {
        // Handle the 'bootstrap' attribute specifically (only this one needs path conversion)
        if (isset($element['bootstrap'])) {
            $relativePath = (string)$element['bootstrap'];
            $absolutePath = realpath($this->routePrefix . $relativePath);
            if ($absolutePath) {
                $element['bootstrap'] = $absolutePath;
            }
        }

        foreach ($element as $child) {
            // If the child is a directory, file, or log target, convert the path
            if (in_array($child->getName(), ['directory', 'file', 'log', 'bootstrap'])) {
                $relativePath = (string)$child;
                $absolutePath = realpath($this->routePrefix . $relativePath);
                if ($absolutePath) {
                    $child[0] = $absolutePath; // Replace the relative path with the absolute path
                }
            }

            // Recursively check nested elements
            $this->convertRelativeToAbsolutePaths($child);
        }
    }
}
