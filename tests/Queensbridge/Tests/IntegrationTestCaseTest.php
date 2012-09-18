<?php

namespace Queensbridge\Tests;

use Queensbridge\IntegrationTestCase;

class IntegrationTestCaseTest extends IntegrationTestCase
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

    function testAdmin()
    {
        $this->visit(admin_url());
        $this->assertTrue(is_admin(), 'This is not an admin page.');
    }

    function testIsEmailOnlyLettersWithDotComDomain() {
        $this->assertEquals( 'nb@nikolay.com', is_email( 'nb@nikolay.com' ) );
    }

    function testIsEmailShouldNotAllowMissingTld() {
        $this->assertFalse( is_email( 'nb@nikolay' ) );
    }

    function testIsEmailShouldAllowBgDomain() {
        $this->assertEquals( 'nb@nikolay.bg', is_email( 'nb@nikolay.bg' ) );
    }

    function testIsEmailShouldNotAllowBlahDomain() {
        $this->assertFalse( is_email( 'nb@nikolay.blah' ) );
    }
}