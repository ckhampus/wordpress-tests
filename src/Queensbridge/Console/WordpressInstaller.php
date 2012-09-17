<?php

namespace Queensbridge\Console;

use Symfony\Component\HttpFoundation\Request;

class WordpressInstaller
{
    private $config;

    public function __contruct()
    {
        $this->config = $config;
    }

    public function install()
    {
        $config = $this->config;

        define('WP_INSTALLING', true);

        // Set table prefix globally.
        global $table_prefix;
        $table_prefix = DB_TABLE_PREFIX;

        // Overide global request variables.
        $request = Request::create('http://'.WP_TESTS_DOMAIN);
        $request->overrideGlobals();
        global $PHP_SELF;
        $PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

        require_once(ABSPATH . '/wp-settings.php');
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        require_once(ABSPATH . '/wp-includes/wp-db.php');

        define( 'WP_TESTS_VERSION_FILE', ABSPATH . '.wp-tests-version' );

        $wpdb->suppress_errors();
        $wpdb->hide_errors();

        $installed = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'" );

        if ( $installed && file_exists( WP_TESTS_VERSION_FILE ) ) {
            $installed_version_hash = file_get_contents( WP_TESTS_VERSION_FILE );
            if ( $installed_version_hash == $this->testVersionCheckHash() ) {
                return;
            }
        }
        $wpdb->query('SET storage_engine = INNODB;');
        $wpdb->query('DROP DATABASE IF EXISTS '.DB_NAME.";");
        $wpdb->query('CREATE DATABASE '.DB_NAME.";");
        $wpdb->select(DB_NAME, $wpdb->dbh);

        echo "Installing WordPress" . PHP_EOL;
        wp_install(WP_TESTS_TITLE, WP_TESTS_ADMIN, WP_TESTS_EMAIL, true, '', 'a');

        if (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) {
            $this->installMultiSite();
        }

        file_put_contents(WP_TESTS_VERSION_FILE, $this->testVersionCheckHash());
    }

    public function installMultiSite()
    {
        echo "Installing WordPress Network" .PHP_EOL;

        define( 'WP_INSTALLING_NETWORK', true );
        //wp_set_wpdb_vars();
        // We need to create references to ms global tables to enable Network.
        foreach ($wpdb->tables( 'ms_global' ) as $table => $prefixed_table) {
            $wpdb->$table = $prefixed_table;
        }

        install_network();

        $result = populate_network(1, WP_TESTS_DOMAIN, WP_TESTS_EMAIL, WP_TESTS_NETWORK_TITLE, '/', WP_TESTS_SUBDOMAIN_INSTALL);

        //require_once ABSPATH . '/wp-settings.php';

        //require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        //require_once ABSPATH . '/wp-includes/wp-db.php';

        echo "Installing Network Sites" . PHP_EOL;
        wp_install( WP_TESTS_TITLE, WP_TESTS_ADMIN, WP_TESTS_EMAIL, true, '', 'a' );

        if (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) {
            $blogs = explode(',', WP_TESTS_BLOGS);
            foreach ($blogs as $blog) {
                if (WP_TESTS_SUBDOMAIN_INSTALL) {
                    $newdomain = $blog.'.'.preg_replace( '|^www\.|', '', WP_TESTS_DOMAIN );
                    $path = $base;
                } else {
                    $newdomain = WP_TESTS_DOMAIN;
                    $path = $base.$blog.'/';
                }

                wpmu_create_blog($newdomain, $path, $blog, email_exists(WP_TESTS_EMAIL) , array('public' => 1), 1);
            }
        }
    }

    /**
     * Generate a hash to be used when comparing installed version against
     * codebase and current configuration
     * @return string $hash sha1 hash
     */
    public function testVersionCheckHash()
    {
        $hash = '';

        $db_version = get_option( 'db_version' );

        if (defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ) {
            $version = $db_version;
            if (defined( 'WP_TESTS_BLOGS' )) {
                $version .= WP_TESTS_BLOGS;
            }
            if ( defined( 'WP_TESTS_SUBDOMAIN_INSTALL' ) ) {
                $version .= WP_TESTS_SUBDOMAIN_INSTALL;
            }
            if ( defined( 'WP_TESTS_DOMAIN' ) ) {
                $version .= WP_TESTS_DOMAIN;
            }

        } else {
            $version = $db_version;
        }

        return sha1($version);
    }
}
