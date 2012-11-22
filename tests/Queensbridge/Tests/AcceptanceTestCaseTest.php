<?php

namespace Queensbridge\Tests;

use Queensbridge\AcceptanceTestCase;

class AcceptanceTestCaseTest extends AcceptanceTestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCall()
    {
        $this->nonExistentMethod();
    }

    public function testFailingAssertions()
    {
        try {
            $this->visit('http://en.wikipedia.org/wiki/Main_Page');
            $this->assertResponseContains('This string should not exist');
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
          // As expected the assertion failed, silently return
          return;
        }
        // The assertion did not fail, make the test fail
        $this->fail('This test did not fail as expected');
    }

    /**
     * @javascript
     */
    public function testWithJavascript()
    {
        $this->visit('http://en.wikipedia.org/wiki/Main_Page');

        $this->fillIn('search', 'Stockholm');
        $this->wait(2000, "$('.suggestions:visible').length > 0");
        $this->find('css', '.suggestions-result:first-child')->click();
        $this->assertElementExists('css','#firstHeading');
        $this->assertResponseContains('Stockholm');

        $this->clickLink('Main page');
    }

    public function testWithoutJavascript()
    {
        $this->setBaseUrl('http://en.wikipedia.org');
        $this->visit('/wiki/Main_Page');
        $this->assertStatusCodeEquals(200);

        $this->fillIn('search', 'Stockholm');
        $this->clickButton('searchButton');
        $this->assertStatusCodeEquals(200);
        $this->assertElementExists('css','#firstHeading');
        $this->assertResponseContains('Stockholm');

        $this->clickLink('Main page');
        $this->assertStatusCodeEquals(200);
    }
}
