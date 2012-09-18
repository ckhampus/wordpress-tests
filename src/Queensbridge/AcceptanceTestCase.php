<?php

namespace Queensbridge;

use Behat\Mink\Driver\Selenium2Driver;
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
        $driver = new Selenium2Driver('firefox', 'base_url');

        $handler  = new SelectorsHandler(array(
            'named' => new NamedSelector(),
            'css' => new CssSelector()
        ));

        $session = new Session($driver, $handler);
        $session->start();

        self::$mink = new Mink();
        self::$mink->registerSession('selenium', $session);
        self::$mink->setDefaultSessionName('selenium');
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
        $el->$this->getPage()->findButton($button);
        $el->click();

        return $el;
    }

    public function clickOn($linkOrButton)
    {
        $el = $this->getPage()->find('named',
            array('link_or_button', $linkOrButton)
        );

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
}
