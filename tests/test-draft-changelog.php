<?php
/**
 * Coverage for bin/draft-changelog.php, run against a throwaway git repository.
 *
 * @package Theme_My_Login
 */

class Test_Draft_Changelog extends WP_UnitTestCase {

	/**
	 * Path to the throwaway repository.
	 *
	 * @var string
	 */
	private $dir;

	public function setUp(): void {
		parent::setUp();

		$this->dir = sys_get_temp_dir() . '/tml-draft-changelog-' . uniqid();
		mkdir( $this->dir . '/bin', 0777, true );
		mkdir( $this->dir . '/src' );

		copy( dirname( __DIR__ ) . '/bin/draft-changelog.php', $this->dir . '/bin/draft-changelog.php' );
		file_put_contents( $this->dir . '/src/readme.txt', "== Changelog ==\n\n= 1.0.0 =\n* Initial release\n" );

		$this->git( 'init -q' );
		$this->commit( 'feat: initial release' );
		$this->git( 'tag v1.0.0' );
	}

	public function tearDown(): void {
		exec( 'rm -rf ' . escapeshellarg( $this->dir ) );

		parent::tearDown();
	}

	public function test_release_note_trailer_replaces_the_subject() {
		$this->commit( 'fix: avoid a double redirect', 'Release-Note: Stop the login page from reloading twice' );

		$this->assertStringContainsString( "= 1.1.0 =\n* Stop the login page from reloading twice\n", $this->draft() );
	}

	public function test_release_note_none_leaves_the_commit_out() {
		$this->commit( 'feat: add a visible feature' );
		$this->commit( 'feat: add a promotional notice', 'Release-Note: none' );
		$this->commit( 'fix: correct another thing', 'Release-Note: NONE' );

		$readme = $this->draft();

		$this->assertStringContainsString( "= 1.1.0 =\n* Add a visible feature\n\n= 1.0.0 =", $readme );
		$this->assertStringNotContainsString( 'promotional', $readme );
	}

	/**
	 * Run the drafter for 1.1.0 and return the resulting readme.
	 *
	 * @return string
	 */
	private function draft() {
		exec( 'cd ' . escapeshellarg( $this->dir ) . ' && ' . escapeshellarg( PHP_BINARY ) . ' bin/draft-changelog.php 1.1.0 2>&1' );

		return file_get_contents( $this->dir . '/src/readme.txt' );
	}

	/**
	 * Create an empty commit in the throwaway repository.
	 *
	 * @param string $subject Commit subject.
	 * @param string $body    Optional commit body.
	 */
	private function commit( $subject, $body = '' ) {
		$args = 'commit -q --allow-empty -m ' . escapeshellarg( $subject );

		if ( $body ) {
			$args .= ' -m ' . escapeshellarg( $body );
		}

		$this->git( $args );
	}

	/**
	 * Run a git command in the throwaway repository.
	 *
	 * @param string $args Arguments to pass to git.
	 */
	private function git( $args ) {
		exec( 'git -C ' . escapeshellarg( $this->dir ) . ' -c user.name=Test -c user.email=test@example.com -c commit.gpgsign=false -c tag.gpgsign=false ' . $args );
	}
}
