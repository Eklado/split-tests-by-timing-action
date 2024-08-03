<?php

namespace Splitter;

use InvalidArgumentException;

class ArgumentHandler
{
    private array $arguments;
    private string $baseRoute = '/github/workspace';

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function getXmlPartialDir(): string
    {
        $xmlPartialDir = getenv('xml-partial-dir');

        if (isset($xmlPartialDir)) {
            $dirName = $this->baseRoute . $xmlPartialDir;

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument `xml-partial-dir`");
    }

    public function getNodeIndex(): ?int
    {
        $nodeIndex = getenv('node-index');

        if (isset($nodeIndex)) {
            return (int)$nodeIndex;
        }

        return null;
    }

    public function getNodeTotal(): int
    {
        $nodeTotal = getenv('node-total');

        if (isset($nodeTotal)) {
            return (int)$nodeTotal;
        }

        throw new InvalidArgumentException("Missing required argument `node-total`");
    }

    public function getJUnitXmlReportDir(): string
    {
        $junitXmlReportDir = getenv('junit-xml-report-dir');

        if (isset($junitXmlReportDir)) {
            $dirName = $this->baseRoute . $junitXmlReportDir;

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument `junit-xml-report-dir`");
    }

    public function hasDebugFlag(): bool
    {
        return (bool)getenv('debug');
    }
}
