<?php

namespace Queensbridge\Tests;

use Queensbridge\AcceptanceTestCase;

class AcceptanceTestCaseTest extends AcceptanceTestCase
{
    public function setUp()
    {
        $this->setBaseUrl('http://127.0.0.1:9292');
    }

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
            $this->visit('/');
            $this->assertResponseContains('This string should not exist');
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
          // As expected the assertion failed, silently return
          return;
        }
        // The assertion did not fail, make the test fail
        $this->fail('This test did not fail as expected');
    }

    public function testWithoutJavascript()
    {
        $this->visit('/');
        $this->assertStatusCodeEquals(200);

        $this->fillForm();
        $this->assertStatusCodeEquals(200);

        $this->assertResponseContains('text-field: Cristian');
        $this->assertResponseContains('select-field: 4');
        $this->assertResponseContains('radio-field: 3');
        $this->assertResponseContains('checkbox-field-1: on');
        $this->assertResponseContains('checkbox-field-2: off');

        $this->clickLink('Go back');
        $this->assertStatusCodeEquals(200);
    }

    /**
     * @javascript
     */
    public function testWithJavascript()
    {
        $this->visit('/');

        $this->fillForm();

        $this->assertResponseContains('text-field: Cristian');
        $this->assertResponseContains('select-field: 4');
        $this->assertResponseContains('radio-field: 3');
        $this->assertResponseContains('checkbox-field-1: on');
        $this->assertResponseContains('checkbox-field-2: off');

        $this->clickLink('Go back');
    }

    public function fillForm()
    {
        $this->fillIn('Text field', 'Cristian');
        $this->select('Option 4', 'Select field');
        $this->choose('Radio option 3');
        $this->check('Checkbox field 1');
        $this->uncheck('Checkbox field 2');
        $this->clickButton('Submit form');
    }
}
