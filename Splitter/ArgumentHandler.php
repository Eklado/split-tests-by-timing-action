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
        $xmlPartialDirIndex = array_search('--xml-partial-dir', $this->arguments);

        if ($xmlPartialDirIndex !== false && isset($this->arguments[$xmlPartialDirIndex + 1])) {
            $dirName = (string)$this->arguments[$xmlPartialDirIndex + 1];

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument --xml-partial-dir");
    }

    public function getNodeIndex(): ?int
    {
        $nodeIndex = array_search('--node-index', $this->arguments);

        if ($nodeIndex !== false && isset($this->arguments[$nodeIndex + 1])) {
            return (int)$this->arguments[$nodeIndex + 1];
        }

        return null;
    }

    public function getNodeTotal(): int
    {
        $nodeTotal = array_search('--node-total', $this->arguments);

        if ($nodeTotal !== false && isset($this->arguments[$nodeTotal + 1])) {
            return (int)$this->arguments[$nodeTotal + 1];
        }

        throw new InvalidArgumentException("Missing required argument --node-total");
    }

    public function getJUnitXmlReportDir(): string
    {
        $junitXmlReportDirIndex = array_search('--junit-xml-report-dir', $this->arguments);

        if ($junitXmlReportDirIndex !== false && isset($this->arguments[$junitXmlReportDirIndex + 1])) {
            $dirName = (string)$this->arguments[$junitXmlReportDirIndex + 1];

            if (is_dir($dirName)) {
                return $dirName;
            }

            throw new InvalidArgumentException("Directory ($dirName) not found, please create it first");
        }

        throw new InvalidArgumentException("Missing required argument --junit-xml-report-dir");
    }

    public function hasDebugFlag(): bool
    {
        return in_array('--debug', $this->arguments);
    }
}
