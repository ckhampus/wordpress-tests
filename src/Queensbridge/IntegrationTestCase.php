<?php

namespace Queensbridge;

use Symfony\Component\HttpFoundation\Request;

abstract class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    private static $testCase;

    protected function prepareEnvironment()
    {
        $annotations = $this->getAnnotations();

        if (!empty($annotations['method'])) {
            if (array_key_exists('ajax', $annotations['method'])) {
                self::$testCase = new \WP_Ajax_UnitTestCase();
            } else if (array_key_exists('xmlrpc', $annotations['method'])) {
                self::$testCase = new \WP_XMLRPC_UnitTestCase();
            }
        }

        if (self::$testCase === null) {
            self::$testCase = new \WP_UnitTestCase();
        }

        self::$testCase->setUp();
    }

    /**
     * Rollback back database.
     */
    protected function resetEnvironment()
    {
        self::$testCase->tearDown();
    }

    public function visit($url)
    {
        self::$testCase->go_to($url);
    }

    public function createPost(array $args = array())
    {
        return self::$testCase->factory->post->create($args);
    }

    public function createComment($postId, array $args = array())
    {
        return $this->createComment($postId, 1, $args);
    }

    public function createComments($postId, $count = 1, array $args = array())
    {
        return self::$testCase->factory->comment->create_post_comments($postId, $count, $args);
    }


    public function createUser(array $args = array())
    {
        return self::$testCase->factory->user->create($args);
    }

    public function createTerm(array $args = array())
    {
        return self::$testCase->factory->term->create($args);
    }

    public function createCategory(array $args = array())
    {
        return self::$testCase->factory->category->create($args);
    }

    public function createTag(array $args = array())
    {
        return self::$testCase->factory->tag->create($args);
    }

    public function runBare()
    {
        $this->prepareEnvironment();
        parent::runBare();
        $this->resetEnvironment();
    }

    public function __call($method, $args)
    {
        $object = new \ReflectionObject(self::$testCase);

        if ($object->hasMethod($method)) {
            $objectMethod = $object->getMethod($method);
            $objectMethod->setAccessible(true);
            return $objectMethod->invokeArgs(self::$testCase, $args);
        }

        if (substr($method, 0, 3) === 'get') {
            $property = substr($method, 3);

            $word = preg_replace('/([A-Z\d]+)([A-Z][a-z])/', '\1_\2', $property);
            $word = preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $word);
            $word = str_replace(' ', '_', $word);
            $word = str_replace('-', '_', $word);
            $property = strtolower($word);

            if ($object->hasProperty('_'.$property)) {
                $objectProperty = $object->getProperty('_'.$property);
                $objectProperty->setAccessible(true);
                return $objectProperty->getValue(self::$testCase);
            }
        }
    }
}
