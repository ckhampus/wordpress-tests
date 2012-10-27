<?php

namespace Queensbridge;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Mink;
use Behat\Mink\Selector\CssSelector;
use Behat\Mink\Selector\NamedSelector;
use Behat\Mink\Selector\SelectorsHandler;
use Behat\Mink\Session;

/**
 * Test case for testing WordPress site in real browsers.
 */
abstract class AcceptanceTestCase  extends \PHPUnit_Framework_TestCase
{
    protected static $mink;

    protected $baseUrl;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        $handler  = new SelectorsHandler(array(
            'named' => new NamedSelector(),
            'css' => new CssSelector()
        ));

        $goutte = new GoutteDriver();
        $noJsSession = new Session($goutte, $handler);

        $selenium = new Selenium2Driver('firefox');
        $jsSession = new Session($selenium, $handler);

        self::$mink = new Mink();
        self::$mink->registerSession('nojs', $noJsSession);
        self::$mink->registerSession('js', $jsSession);
        self::$mink->setDefaultSessionName('nojs');
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $annotations = $this->getAnnotations();

        if (!empty($annotations['method'])) {
            if (array_key_exists('javascript', $annotations['method'])) {
                self::$mink->setDefaultSessionName('js');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        self::$mink->setDefaultSessionName('nojs');
        self::$mink->resetSessions();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        if (null !== self::$mink) {
            self::$mink->stopSessions();
        }
    }

    /**
     * Returns the currently active session.
     * @return Behat\Mink\Session The session.
     */
    public function getSession()
    {
        return self::$mink->getSession();
    }

    public function getMink()
    {
        if (null === self::$mink) {
            throw new \RuntimeException(
                'Mink is not initialized. Forgot to call parent context setUpBeforeClass()?'
            );
        }

        return self::$mink;
    }

    /**
     * Set the base url.
     * @param string $url The base url.
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    /**
     * Visit specified URL.
     *
     * @param string $url URL of the page.
     */
    public function visit($url)
    {
        $this->getSession()->visit($url);
    }

    public function clickLink($link)
    {
        $el = $this->findLink($link);
        $el->click();
    }

    public function clickButton($button)
    {
        $el = $this->findButton($button);
        $el->click();
    }

    public function fillIn($field, $value)
    {
        $el = $this->findField($field);
        $el->setValue($value);
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
    }

    public function uncheck($checkbox)
    {
        $el = $this->findField($checkbox);

        if ($el->isChecked()) {
            $el->uncheck();
        }
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

    public function __call($method, $args)
    {
        if (strpos($method, 'assert') === 0) {
            $asserter = self::$mink->assertSession();

            $method = str_replace('assert', '', $method);
            $method = lcfirst($method);

            $object = new \ReflectionObject($asserter);

            if ($object->hasMethod($method)) {
                try {
                    $objectMethod = $object->getMethod($method);
                    $objectMethod->invokeArgs($asserter, $args);
                    $this->assertTrue(true);
                } catch (\Exception $e) {
                    $this->assertTrue(false, $e->getMessage());
                }

                return;
            }
        } else {
            $session = $this->getSession();

            $object = new \ReflectionObject($session);

            if ($object->hasMethod($method)) {
                $objectMethod = $object->getMethod($method);
                return $objectMethod->invokeArgs($session, $args);
            }
        }

        throw new \BadMethodCallException("Call to a member function {$method} on a non-object");
        
    }
}
