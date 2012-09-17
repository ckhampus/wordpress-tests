<?php

namespace Queensbridge;

use Symfony\Component\HttpFoundation\Request;

class WordpressTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $wpdb;
        $wpdb->suppress_errors = false;
        $wpdb->show_errors = true;
        $wpdb->db_connect();
        ini_set('display_errors', 1 );
        $this->clearGlobals();
        $this->startTransaction();
    }

    public function tearDown()
    {
        global $wpdb;
        $wpdb->query('ROLLBACK');
    }

    public function clearGlobals()
    {
        $_GET = array();
        $_POST = array();
        $_REQUEST = array();
        $_SERVER = array();
        $_COOKIE = array();
        $_FILES = array();

        $this->flushCache();
    }

    public function flushCache()
    {
        global $wp_object_cache;
        $wp_object_cache->group_ops = array();
        $wp_object_cache->stats = array();
        $wp_object_cache->memcache_debug = array();
        $wp_object_cache->cache = array();

        if (method_exists($wp_object_cache, '__remoteset')) {
            $wp_object_cache->__remoteset();
        }
        wp_cache_flush();
    }

    public function startTransaction()
    {
        global $wpdb;
        $wpdb->query('SET autocommit = 0;');
        $wpdb->query('START TRANSACTION;');
    }

    public function assertWPError( $actual, $message = '' )
    {
        $this->assertTrue( is_wp_error( $actual ), $message );
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
    }

    protected function initialize()
    {

    }

    protected function initializeAdmin()
    {

    }
}
