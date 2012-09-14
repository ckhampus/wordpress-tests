<?php

namespace Queensbridge\Tests;

use Queensbridge\WordpressTestCase;

class WordpressTestCaseTest extends WordpressTestCase
{
    function testSingle() {
        $this->visit(get_permalink(1));
        $this->assertTrue(is_single(), 'This is not a single post page.');
        $this->assertTrue(have_posts());
    }

    function test404() {
        $this->visit(site_url('?p=100'));
        $this->assertTrue(is_404());
    }
}