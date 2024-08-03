<?php

namespace Splitter;

use DOMDocument;

class XMLHandler
{
    private string $junitXmlReportDir;

    public function __construct(string $junitXmlReportDir)
    {
        $this->junitXmlReportDir = $junitXmlReportDir;
    }

    public function getTestFileResults(): array
    {
        $xmlFiles = glob(__DIR__ . '/../' . $this->junitXmlReportDir . '/*.xml');
        $testFileResults = [];

        foreach ($xmlFiles as $xmlFile) {
            $xml = new DOMDocument();
            $xml->load($xmlFile);
            $this->accumulateTime($xml, $testFileResults);
        }

        arsort($testFileResults);
        return $testFileResults;
    }

    private function accumulateTime(DOMDocument $testResultXml, array &$testFileResults): void
    {
        $testSuites = $testResultXml->getElementsByTagName('testsuite');

        foreach ($testSuites as $testSuite) {
            $file = $testSuite->getAttribute('file');
            $time = (float)$testSuite->getAttribute('time');

            if ($file) {
                if (isset($testFileResults[$file])) {
                    $testFileResults[$file] += $time;
                } else {
                    $testFileResults[$file] = $time;
                }
            }
        }
    }
}
