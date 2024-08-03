# Split PHP Tests by Timing Action

## Overview

The **Split PHP Tests by Timing** action is a custom PHP action designed to split PHPUnit test files by timing.
This action is optimized for use with the Laravel framework, handling tests located in both `tests/Unit` and `tests/Feature` directories.
It is useful for optimizing test execution by distributing tests across multiple parallel nodes.

## Inputs

The action accepts the following inputs:

### `junit-xml-report-dir`

- **Description**: The directory path where the last test JUnit reports exist.
- **Required**: `true`

### `node-total`

- **Description**: The total number of expected parallel nodes.
- **Required**: `true`

### `node-index`

- **Description**: The index of the current node.
- **Required**: `false`
- **Default**: `null`

### `xml-partial-dir`

- **Description**: The directory path where the partial PHPUnit XML files will be placed.
- **Required**: `true`

### `debug`

- **Description**: Debug option to echo some data that enables following up the flow.
- **Required**: `false`
- **Default**: `false`

## Usage

To use this action in your GitHub Actions workflow, add the following step to your workflow YAML file:

```yaml
- name: Create partial PHPUnit XML files
  uses: Eklado/split-tests-by-timing-action@v1
  with:
    junit-xml-report-dir: '/tmp/junit_xml_download_dir'
    node-total: 10
    node-index: 0
    xml-partial-dir: '/tmp/xml_partial_dir'
    debug: 'false'
```

### Example Workflow

Here is a sample workflow that uses this action:

```yaml
name: Split PHP Tests

on: [push]

jobs:
  split-tests:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'

      - name: Install dependencies
        run: composer install

      - name: Create partial PHPUnit XML files
        uses: Eklado/split-tests-by-timing-action@v1
        with:
          junit-xml-report-dir: '/tmp/junit_xml_download_dir'
          node-total: 10
          node-index: 0
          xml-partial-dir: '/tmp/xml_partial_dir'
          debug: 'false'
```

## How It Works

1. **Inputs**: The action takes directory paths, node information, and a debug flag as inputs.
2. **Execution**: The action runs a PHP script (`phpunit-xml-splitter.php`) which performs the logic to split the PHPUnit XML files.
3. **Laravel Compatibility**: This action is designed specifically to work with Laravel applications, handling test files located in both `tests/Unit` and `tests/Feature` directories.
4. **Debugging**: If the `debug` input is set to `'true'`, additional debugging information will be echoed.

## Troubleshooting

- **File Not Found**: Ensure that the paths provided for `junit-xml-report-dir` and `xml-partial-dir` are correct and accessible.
- **Permissions**: Verify that the GitHub Actions runner has the necessary permissions to access the directories and files specified.

## Contributing

If you encounter any issues or have suggestions for improvements, please open an issue or submit a pull request on [GitHub](https://github.com/Eklado/split-tests-by-timing-action).
