#!/usr/bin/env php
<?php

/**
 * Drafts a WordPress-style readme.txt changelog entry from Conventional
 * Commits since the last tag.
 *
 * This is a mechanical first draft only, meant to be hand-edited (wording,
 * folding duplicates, "Tested up to" bullets) while reviewing the Release PR
 * it gets committed onto - not a finished changelog. See CONTRIBUTING.md.
 *
 * Usage: php bin/draft-changelog.php <version>
 */

$version = $argv[1] ?? null;

if ( ! $version ) {
	fwrite( STDERR, "Usage: php bin/draft-changelog.php <version>\n" );
	exit( 1 );
}

// Types that never surface in the user-facing changelog.
$excluded_types = array( 'chore', 'ci', 'docs', 'build', 'test', 'refactor' );

$last_tag = trim( (string) shell_exec( 'git describe --tags --abbrev=0 2>/dev/null' ) );
$range    = $last_tag ? "{$last_tag}..HEAD" : 'HEAD';

$record_sep = "\x1e";
$field_sep  = "\x1f";

$log     = (string) shell_exec(
	'git log --no-merges ' . escapeshellarg( $range ) . " --pretty=format:%s{$field_sep}%b{$record_sep}"
);
$records = array_filter(
	array_map( 'trim', explode( $record_sep, $log ) ),
	static function ( $record ) {
		return '' !== $record;
	}
);

$bullets = array();

foreach ( $records as $record ) {
	list( $subject, $body ) = array_pad( explode( $field_sep, $record, 2 ), 2, '' );

	if ( ! preg_match( '/^(\w+)(\([^)]*\))?:\s*(.+)$/', $subject, $matches ) ) {
		continue; // Not a Conventional Commits subject, skip it.
	}

	$type = strtolower( $matches[1] );

	if ( in_array( $type, $excluded_types, true ) ) {
		continue;
	}

	// A `Release-Note:` trailer overrides the subject for user-facing wording.
	if ( preg_match( '/^Release-Note:\s*(.+)$/mi', $body, $note ) ) {
		$text = trim( $note[1] );
	} else {
		$text = trim( $matches[3] );
	}

	$bullets[] = '* ' . ucfirst( $text );
}

if ( empty( $bullets ) ) {
	fwrite( STDERR, "No feat/fix/perf commits found since {$last_tag}; nothing to draft.\n" );
	exit( 0 );
}

$entry = "= {$version} =\n" . implode( "\n", $bullets );

$readme_path = __DIR__ . '/../src/readme.txt';
$readme      = file_get_contents( $readme_path );

if ( false === $readme ) {
	fwrite( STDERR, "Could not read {$readme_path}\n" );
	exit( 1 );
}

$marker = '== Changelog ==';
$pos    = strpos( $readme, $marker );

if ( false === $pos ) {
	fwrite( STDERR, "Could not find '== Changelog ==' in readme.txt\n" );
	exit( 1 );
}

$insert_at = $pos + strlen( $marker );
$readme    = substr( $readme, 0, $insert_at ) . "\n\n" . $entry . substr( $readme, $insert_at );

file_put_contents( $readme_path, $readme );

fwrite( STDOUT, "Drafted changelog entry for {$version}:\n\n{$entry}\n" );
