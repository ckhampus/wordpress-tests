<?php

/**
 * Sets up testing environment and load test configuration.
 *
 * @param  string $configPath The configuration file path.
 */
function setup_test_environment($configPath) {
    if (file_exists($configPath)) {
        define('WP_TESTS_CONFIG_PATH', $configPath);

        if (file_exists(__DIR__.'/../lib/bootstrap.php')) {
            return include __DIR__.'/../lib/bootstrap.php';
        }
    }
}