<?php

namespace Queensbridge;

use Behat\Mink\Driver\ZombieDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Driver\NodeJS\Server\ZombieServer;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Mink\Selector\NamedSelector;
use Behat\Mink\Selector\CssSelector;
use Behat\Mink\Selector\SelectorsHandler;
use Behat\Mink\Exception\UnsupportedDriverActionException;

class AcceptanceTestCase  extends \PHPUnit_Framework_TestCase
{
    private static $mink;

    public static function setUpBeforeClass()
    {
        $handler  = new SelectorsHandler(array(
            'named' => new NamedSelector(),
            'css' => new CssSelector()
        ));

        $goutte = new GoutteDriver();
        $noJsSession = new Session($goutte, $handler);

        //$zombie = new ZombieDriver(new ZombieServer());
        $selenium = new Selenium2Driver('firefox');
        $jsSession = new Session($selenium, $handler);

        self::$mink = new Mink();
        self::$mink->registerSession('nojs', $noJsSession);
        self::$mink->registerSession('js', $jsSession);
        self::$mink->setDefaultSessionName('nojs');
    }

    public function setUp()
    {

    }

    public function tearDown()
    {
        self::$mink->setDefaultSessionName('nojs');
        self::$mink->resetSessions();
    }

    public static function tearDownAfterClass()
    {
        self::$mink->restartSessions();
    }

    public function enableJavascript()
    {
        self::$mink->setDefaultSessionName('js');
    }

    public function getSession()
    {
        return self::$mink->getSession();
    }

    public function visit($url)
    {
        return $this->getSession()->visit($url);
    }

    public function getPage()
    {
        return $this->getSession()->getPage();
    }

    public function clickLink($link)
    {
        $el = $this->findLink($link);
        $el->click();

        return $el;
    }

    public function clickButton($button)
    {
        $el = $this->findButton($button);
        $el->click();

        return $el;
    }

    public function clickOn($linkOrButton)
    {
        $el = $this->find('named', array('link_or_button', $linkOrButton));
        $el->click();

        return $el;
    }

    public function fillIn($field, $value)
    {
        $el = $this->findField($field);
        $el->setValue($value);

        return $el;
    }

    public function choose($name)
    {

    }

    public function check($checkbox)
    {
        $el = $this->findField($checkbox);

        if (!$el->isChecked()) {
            $el->check();
        }

        return $el;
    }

    public function uncheck($checkbox)
    {
        $el = $this->findField($checkbox);

        if ($el->isChecked()) {
            $el->uncheck();
        }

        return $el;
    }

    public function select($option, $selectBox)
    {
        $this->findField($selectBox)->selectOption($option);
    }

    public function find($handler, $value)
    {
        return $this->getPage()->find($handler, $value);
    }

    public function findLink($link)
    {
        return $this->getPage()->findLink($link);
    }

    public function findButton($button)
    {
        return $this->getPage()->findButton($button);
    }

    public function findField($field)
    {
        return $this->getPage()->findField($field);
    }

    public function assertStatusCodeEquals($code)
    {
        try {
            $this->assertEquals($code, $this->getSession()->getStatusCode());
        } catch (UnsupportedDriverActionException $e) { }
    }

    public function assertPageHasContent($content)
    {
        $el = $this->find('named', array('content', $content));
        $this->assertNotNull($el);
    }

    public function assertPageHasSelector($selector)
    {
        $el = $this->find('css', $selector);
        $this->assertNotNull($el);
    }
}
