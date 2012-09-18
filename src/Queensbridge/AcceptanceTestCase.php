<?php

namespace Queensbridge;

use Behat\Mink\Driver\ZombieDriver;
use Behat\Mink\Driver\NodeJS\Server\ZombieServer;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Mink\Selector\NamedSelector;
use Behat\Mink\Selector\CssSelector;
use Behat\Mink\Selector\SelectorsHandler;

class AcceptanceTestCase  extends \PHPUnit_Framework_TestCase
{
    private static $mink;

    public static function setUpBeforeClass()
    {
        //$driver = new Selenium2Driver('firefox', 'base_url');
        //$server = new ZombieServer('127.0.0.1', '1337');
        //$driver = new ZombieDriver($server);

        $handler  = new SelectorsHandler(array(
            'named' => new NamedSelector(),
            'css' => new CssSelector()
        ));

        $goutte = new GoutteDriver();
        $noJsSession = new Session($goutte, $handler);

        $zombie = new ZombieDriver(new ZombieServer());
        $jsSession = new Session($zombie, $handler);

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
        $el = $this->getPage()->findLink($link);
        $el->click();

        return $el;
    }

    public function clickButton($button)
    {
        $el = $this->getPage()->findButton($button);
        $el->click();

        return $el;
    }

    public function clickOn($linkOrButton)
    {
        $el = $this->getPage()->find('named', array('link_or_button', $linkOrButton));
        $el->click();

        return $el;
    }

    public function fillIn($field, $value)
    {
        $el = $this->getPage()->findField($field);
        $el->setValue($value);

        return $el;
    }

    public function choose($name)
    {

    }

    public function check($checkbox)
    {
        $el = $this->getPage()->findField($checkbox);

        if (!$el->isChecked()) {
            $el->check();
        }

        return $el;
    }

    public function uncheck($checkbox)
    {
        $el = $this->getPage()->findField($checkbox);

        if ($el->isChecked()) {
            $el->uncheck();
        }

        return $el;
    }

    public function select($option, $selectBox)
    {
        $this->getPage()->findField($selectBox)->selectOption($option);
    }

    public function assertStatusCodeEquals($code)
    {
        $this->assertEquals($code, $this->getSession()->getStatusCode());
    }
}
