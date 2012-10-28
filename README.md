# WordPress Tests
This is a library for writing tests for plugins, themes and sites. 

## Unit tests
The `UnitTestCase` class is used just like the normal `PHPUnit_Framework_TestCase` class. Right it is basically just a shortcut.

## Integration tests
The `IntegrationTestCase` class allows you to test against and use WordPress functions in your tests.

```php
class SinglePageTest extends IntegrationTestCase
{
    public function testSingle()
    {
        $this->visit(get_permalink(1));
        $this->assertTrue(is_single());
        $this->assertTrue(have_posts());
    }
}
```

## Acceptance tests
The `AcceptanceTestCase` class uses Mink and allows you to test the output of your site. Depending if you require JavaScript for your test or not, this test case will either use Selenium 2 or Goutte as drivers.

```php
class WikipediaSearchTest extends AcceptanceTestCase
{
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
```


