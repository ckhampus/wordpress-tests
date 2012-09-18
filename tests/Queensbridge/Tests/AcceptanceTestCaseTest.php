<?php

namespace Queensbridge\Tests;

use Queensbridge\AcceptanceTestCase;

class AcceptanceTestCaseTest extends AcceptanceTestCase
{
    public function testWithJavascript()
    {
        $this->enableJavascript();

        $this->visit('http://en.wikipedia.org/wiki/Main_Page');
        $this->assertStatusCodeEquals(200);

        $this->fillIn('search', 'Stockholm');
        $this->clickButton('searchButton');
        $this->assertStatusCodeEquals(200);
        $this->assertPageHasSelector('#firstHeading');

        $this->clickLink('Main page');
        $this->assertStatusCodeEquals(200);
    }

    public function testWithoutJavascript()
    {
        $this->visit('http://en.wikipedia.org/wiki/Main_Page');
        $this->assertStatusCodeEquals(200);

        $this->fillIn('search', 'Stockholm');
        $this->clickOn('searchButton');
        $this->assertStatusCodeEquals(200);
        $this->assertPageHasContent('Stockholm');

        $this->clickLink('Main page');
        $this->assertStatusCodeEquals(200);
    }
}
