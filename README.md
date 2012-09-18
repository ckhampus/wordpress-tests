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

When doing integration tests it's possible to tell the `bootstrap`-script to download WordPress and to install it.

## Acceptance tests
The `AcceptanceTestCase` class allows you to test the output of your site.

```php
class WikipediaSearchTest extends AcceptanceTestCase
{
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
```


