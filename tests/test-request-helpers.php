<?php
/**
 * Coverage for tml_get_request_value(), which every form handler uses to
 * read submitted data.
 *
 * @package Theme_My_Login
 */

class Test_Request_Helpers extends WP_UnitTestCase {

	public function tearDown(): void {
		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();

		parent::tearDown();
	}

	public function test_post_type_only_reads_from_post() {
		$_GET['user_login']  = 'from-get';
		$_POST['user_login'] = 'from-post';

		$this->assertSame( 'from-post', tml_get_request_value( 'user_login', 'post' ) );
	}

	public function test_get_type_only_reads_from_get() {
		$_GET['user_login']  = 'from-get';
		$_POST['user_login'] = 'from-post';

		$this->assertSame( 'from-get', tml_get_request_value( 'user_login', 'get' ) );
	}

	public function test_any_type_falls_back_to_request() {
		$_REQUEST['redirect_to'] = 'https://example.org/somewhere';

		$this->assertSame( 'https://example.org/somewhere', tml_get_request_value( 'redirect_to' ) );
	}

	public function test_missing_key_returns_empty_string() {
		$this->assertSame( '', tml_get_request_value( 'does-not-exist' ) );
	}

	public function test_value_is_unslashed() {
		$_POST['user_login'] = "O\\'Brien";

		$this->assertSame( "O'Brien", tml_get_request_value( 'user_login', 'post' ) );
	}
}
