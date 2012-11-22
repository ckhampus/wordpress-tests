<?php

/**
 * Sets up testing environment and load test configuration.
 *
 * @param  string $configPath The configuration file path.
 */
function setup_test_environment($configPath) {
    if (file_exists($configPath)) {
        define('WP_TESTS_CONFIG_PATH', $configPath);

        $libraryPath = '/wordpress-tests-library/bootstrap.php';

        if (file_exists(__DIR__.'/../vendor/queensbridge'.$libraryPath)) {
            return include __DIR__.'/../vendor/queensbridge'.$libraryPath;
        }

        if (file_exists(__DIR__.'/../../'.$libraryPath)) {
            return include __DIR__.'/../../'.$libraryPath;
        }
    }
}