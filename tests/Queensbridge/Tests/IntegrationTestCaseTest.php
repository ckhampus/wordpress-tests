<?php

namespace Queensbridge\Tests;

use Queensbridge\IntegrationTestCase;

/**
 * Admin ajax functions to be tested.
 */
require_once( ABSPATH . 'wp-admin/includes/ajax-actions.php' );

class IntegrationTestCaseTest extends IntegrationTestCase
{
    protected $commentPost = null;

    public function setUp()
    {
        $postId = $this->createPost();
        $this->createComments($postId, 5);
        $this->commentPost = get_post($postId);

        $postId = $this->createPost();
        $this->noCommentPost = get_post($postId);
    }

    public function testSingle()
    {
        $postId = $this->createPost();

        $this->visit(get_permalink($postId));
        $this->assertTrue(is_single(), 'This is not a single post page.');
        $this->assertTrue(have_posts());
    }

    public function test404()
    {
        $this->visit(site_url('?p=100'));
        $this->assertTrue(is_404());
    }

    /**
     * @ajax
     */
    public function testAsAdmin() {

        // Become an administrator
        $this->_setRole('administrator');

        // Set up a default request
        $_POST['_ajax_nonce'] = wp_create_nonce('get-comments');
        $_POST['action']      = 'get-comments';
        $_POST['p']           = $this->commentPost->ID;

        // Make the request
        try {
            $this->_handleAjax('get-comments');
        } catch (\WPAjaxDieContinueException $e) {
            unset($e);
        }

        // Get the response
        $xml = simplexml_load_string($this->getLastResponse(), 'SimpleXMLElement', LIBXML_NOCDATA);

        // Check the meta data
        $this->assertEquals(1, (string) $xml->response[0]->comments['position']);
        $this->assertEquals(0, (string) $xml->response[0]->comments['id']);
        $this->assertEquals('get-comments_0', (string) $xml->response['action']);

        // Check the payload
        $this->assertNotEmpty((string) $xml->response[0]->comments[0]->response_data);

        // And supplemental is empty
        $this->assertEmpty((string) $xml->response[0]->comments[0]->supplemental);
    }
}
