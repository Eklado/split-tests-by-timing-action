<?php

namespace Splitter;

use InvalidArgumentException;

class ArgumentHandler
{
    private array $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function getXmlPartialDir(): string
    {
        $xmlPartialDir = getenv('xml-partial-dir');

        if (isset($xmlPartialDir)) {
            if (is_dir($xmlPartialDir)) {
                return $xmlPartialDir;
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
            if (is_dir($junitXmlReportDir)) {
                return $junitXmlReportDir;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument --junit-xml-report-dir");
    }

    public function hasDebugFlag(): bool
    {
        return (bool)getenv('debug');
    }
}
