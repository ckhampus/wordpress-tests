<?php

namespace Queensbridge;

class WordpressTestSuite extends \PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        return new WordpressTestSuite('WordPress');
    }

    protected function setUp()
    {
        print "\nWordpressTestSuite::setUp()";
    }

    protected function tearDown()
    {
        print "\nWordpressTestSuite::tearDown()";
    }
}