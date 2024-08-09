<?php

namespace Splitter;

use InvalidArgumentException;

class ArgumentHandler
{
    private string $baseRoute = '/';

    public function getXmlPartialDir(): string
    {
        $xmlPartialDir = getenv('INPUT_XML_PARTIAL_DIR');

        if (isset($xmlPartialDir)) {
            $dirName = $this->baseRoute . ltrim($xmlPartialDir, '/');

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument `xml-partial-dir`");
    }

    public function getNodeIndex(): ?int
    {
        $nodeIndex = getenv('INPUT_NODE_INDEX');

        if (isset($nodeIndex)) {
            return (int)$nodeIndex;
        }

        return null;
    }

    public function getNodeTotal(): int
    {
        $nodeTotal = getenv('INPUT_NODE_TOTAL');

        if (isset($nodeTotal)) {
            return (int)$nodeTotal;
        }

        throw new InvalidArgumentException("Missing required argument `node-total`");
    }

    public function getJUnitXmlReportDir(): string
    {
        $junitXmlReportDir = getenv('INPUT_JUNIT_XML_REPORT_DIR');

        if (isset($junitXmlReportDir)) {
            $dirName = $this->baseRoute . ltrim($junitXmlReportDir, '/');

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument `junit-xml-report-dir`");
    }

    public function getTestDirectories(): array
    {
        return array_unique(preg_split('/\s*,\s*/', getenv('INPUT_TEST_DIRECTORIES')));
    }

    public function hasDebugFlag(): bool
    {
        return getenv('INPUT_DEBUG') == 'true';
    }

    public function getBasePath(): string
    {
        return getenv('BASE_PATH');
    }
}
