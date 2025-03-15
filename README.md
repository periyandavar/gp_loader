<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="Application Test Suite">
            <directory>./tests</directory> <!-- Adjust this path to your tests directory -->
        </testsuite>
    </testsuites>
    <php>
        <ini name="error_reporting" value="-1"/> <!-- Report all errors -->
        <ini name="display_errors" value="1"/> <!-- Display errors -->
    </php>
    <logging>
        <log type="junit" target="build/logs/junit.xml"/> <!-- JUnit log file -->
        <log type="coverage-html" target="build/coverage"/> <!-- HTML coverage report -->
    </logging>
</phpunit>