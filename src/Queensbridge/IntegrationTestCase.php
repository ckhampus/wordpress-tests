<?php

namespace Queensbridge;

use Symfony\Component\HttpFoundation\Request;

abstract class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    private $backup = array();

    protected function prepareEnvironment()
    {
        $this->backup['_SERVER'] = $_SERVER;

        $GLOBALS['wpdb']->suppress_errors = false;
        $GLOBALS['wpdb']->show_errors = true;
        $GLOBALS['wpdb']->db_connect();

        ini_set('display_errors', 1);

        $this->clearGlobals();
        $this->startTransaction();
    }

    /**
     * Rollback back database.
     */
    protected function resetEnvironment()
    {
        $GLOBALS['wpdb']->query('ROLLBACK');

        $_SERVER = $this->backup['_SERVER'];
    }

    /**
     * Clear all global variables and flush cache.
     */
    protected function clearGlobals()
    {
        $_GET = array();
        $_POST = array();
        $_REQUEST = array();
        $_SERVER = array();
        $_COOKIE = array();
        $_FILES = array();

        $this->flushCache();
    }

    /**
     * Flush WordPress object cache.
     */
    protected function flushCache()
    {
        $GLOBALS['wp_object_cache']->group_ops = array();
        $GLOBALS['wp_object_cache']->stats = array();
        $GLOBALS['wp_object_cache']->memcache_debug = array();
        $GLOBALS['wp_object_cache']->cache = array();

        if (method_exists($GLOBALS['wp_object_cache'], '__remoteset')) {
            $GLOBALS['wp_object_cache']->__remoteset();
        }
        wp_cache_flush();
    }

    /**
     * Start database transaction.
     */
    protected function startTransaction()
    {
        $GLOBALS['wpdb']->query('SET autocommit = 0;');
        $GLOBALS['wpdb']->query('START TRANSACTION;');
    }

    public function assertWPError( $actual, $message = '' )
    {
        $this->assertTrue(is_wp_error( $actual ), $message);
    }

    public function visit($url)
    {
        $this->clearGlobals();

        $vars = array(
            'query_string',
            'id',
            'postdata',
            'authordata',
            'day',
            'currentmonth',
            'page',
            'pages',
            'multipage',
            'more',
            'numpages',
            'pagenow'
        );

        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                unset($GLOBALS[$var]);
            }
        }

        $request = Request::create($url);
        $request->overrideGlobals();

        unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
        $GLOBALS['wp_the_query'] =& new \WP_Query();
        $GLOBALS['wp_query'] =& $GLOBALS['wp_the_query'];
        $GLOBALS['wp'] =& new \WP();

        // clean out globals to stop them polluting wp and wp_query
        foreach ($GLOBALS['wp']->public_query_vars as $v) {
            unset($GLOBALS[$v]);
        }
        foreach ($GLOBALS['wp']->private_query_vars as $v) {
            unset($GLOBALS[$v]);
        }

        $GLOBALS['wp']->main($request->getQueryString());

        if (strpos($request->getPathInfo(), 'wp-admin')) {
            $this->initializeAdmin();
        }
    }

    protected function initializeAdmin()
    {
        if (!defined('WP_ADMIN')) {
            define('WP_ADMIN', true);
        }

        if (!defined('WP_NETWORK_ADMIN')) {
            define('WP_NETWORK_ADMIN', false);
        }

        if (!defined('WP_USER_ADMIN')) {
            define('WP_USER_ADMIN', false);
        }

        if (!WP_NETWORK_ADMIN && ! WP_USER_ADMIN) {
            define('WP_BLOG_ADMIN', true);
        }

        if (isset($_GET['import']) && !defined('WP_LOAD_IMPORTERS')) {
            define('WP_LOAD_IMPORTERS', true);
        }

        require_once(ABSPATH . 'wp-admin/includes/admin.php');

        if (WP_NETWORK_ADMIN) {
            require(ABSPATH . 'wp-admin/network/menu.php');
        } elseif (WP_USER_ADMIN) {
            require(ABSPATH . 'wp-admin/user/menu.php');
        } else {
            require(ABSPATH . 'wp-admin/menu.php');
        }

        do_action('admin_init');
    }

    public function runTest()
    {
        $this->prepareEnvironment();

        $result = parent::runTest();

        $this->resetEnvironment();

        return $result;
    }
}
