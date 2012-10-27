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
    protected $mink = null;

    protected $baseUrl = null;

    public function setUpSessions()
    {
        if ($this->mink === null) {
            $handler  = new SelectorsHandler(array(
                'named' => new NamedSelector(),
                'css' => new CssSelector()
            ));

            $goutte = new GoutteDriver();
            $noJsSession = new Session($goutte, $handler);

            $selenium = new Selenium2Driver('firefox');
            $jsSession = new Session($selenium, $handler);

            $this->mink = new Mink();
            $this->mink->registerSession('nojs', $noJsSession);
            $this->mink->registerSession('js', $jsSession);
            $this->mink->setDefaultSessionName('nojs');
        }

        $annotations = $this->getAnnotations();

        if (!empty($annotations['method'])) {
            if (array_key_exists('javascript', $annotations['method'])) {
                $this->mink->setDefaultSessionName('js');
            }
        }
    }

    public function tearDownSessions($value='')
    {
        if (null !== $this->mink) {
            $this->mink->setDefaultSessionName('nojs');
            $this->mink->stopSessions();
        }
    }

    /**
     * Returns the currently active session.
     * @return Behat\Mink\Session The session.
     */
    public function getSession()
    {
        return $this->mink->getSession();
    }

    public function getMink()
    {
        if (null === $this->mink) {
            throw new \RuntimeException(
                'Mink is not initialized. Forgot to call parent context setUpBeforeClass()?'
            );
        }

        return $this->mink;
    }

    /**
     * Set the base url.
     *
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
        if ($this->baseUrl !== null) {
            $url = $baseUrl.$url;
        }

        $this->getSession()->visit($url);
    }

    /**
     * Finds a link by id or text and clicks it.
     *
     * @param string $locator Text, id or text of link.
     */
    public function clickLink($locator)
    {
        $el = $this->findLink($locator);
        $el->click();
    }

    /**
     * Finds a button by id, text or value and clicks it.
     *
     * @param string $locator Text, id or value of button.
     */
    public function clickButton($locator)
    {
        $el = $this->findButton($locator);
        $el->click();
    }

    /**
     * Locate a text field or text area and fill it in with the given
     * text The field can be found via its name, id or label text.
     *
     * @param string $locator Which field to fill in.
     * @param string $value   Value to fill in.
     */
    public function fillIn($locator, $value)
    {
        $el = $this->findField($locator);
        $el->setValue($value);
    }

    /**
     * Find a radio button and mark it as checked. The radio button
     * can be found via name, id or label text.
     *
     * @param string $name Which radio button to choose.
     */
    public function choose($locator)
    {

    }

    /**
     * Find a check box and mark it as checked. The check box can be found
     * via name, id or label text.
     *
     * @param string $locator Which check box to check.
     */
    public function check($locator)
    {
        $el = $this->findField($locator);

        if (!$el->isChecked()) {
            $el->check();
        }
    }

    /**
     * Find a check box and mark uncheck it. The check box can be found
     * via name, id or label text.
     *
     * @param string $locator Which check box to uncheck.
     */
    public function uncheck($locator)
    {
        $el = $this->findField($locator);

        if ($el->isChecked()) {
            $el->uncheck();
        }
    }

    /**
     * Find a select box on the page and select a particular option from it.
     * If the select box is a multiple select, select can be called multiple
     * times to select more than one option. The select box can be found
     * via its name, id or label text.
     *
     * @param string $option  Which option to select.
     * @param string $locator Id, name or label of the select box
     */
    public function select($option, $locator)
    {
        $this->findField($locator)->selectOption($option);
    }

    public function find($handler, $value)
    {
        return $this->getPage()->find($handler, $value);
    }

    /**
     * Find a link on the page. The link can be found by its id or text.
     *
     * @param  string                         $locator Which link to find.
     * @return Behat\Mink\Element\NodeElement The found element.
     */
    public function findLink($locator)
    {
        return $this->getPage()->findLink($locator);
    }

    /**
     * Find a button on the page. The button can be found by its id, name or value
     *
     * @param  string                         $locator Which button to find.
     * @return Behat\Mink\Element\NodeElement The found element.
     */
    public function findButton($locator)
    {
        return $this->getPage()->findButton($locator);
    }

    /**
     * Find a form field on the page. The field can be found by its name, id or label text.
     *
     * @param  string                         $locator Which field to find.
     * @return Behat\Mink\Element\NodeElement The found element.
     */
    public function findField($locator)
    {
        return $this->getPage()->findField($locator);
    }

    public function run(\PHPUnit_Framework_TestResult $result = NULL)
    {
        $this->setUpSessions();

        parent::run($result);

        $this->tearDownSessions();

        return $result;
    }

    public function __call($method, $args)
    {
        if (strpos($method, 'assert') === 0) {
            $asserter = $this->mink->assertSession();

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
