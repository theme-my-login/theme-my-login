#!/usr/bin/env php
<?php

/**
 * Drafts a WordPress-style readme.txt changelog entry from Conventional
 * Commits since the last tag.
 *
 * This is a mechanical first draft only, meant to be hand-edited (wording,
 * folding duplicates, "Tested up to" bullets) while reviewing the Release PR
 * it gets committed onto - not a finished changelog. See
 * https://github.com/theme-my-login/.github/blob/main/CONTRIBUTING.md.
 *
 * Safe to re-run: replaces any existing block for the given version
 * instead of appending a duplicate. Re-running discards hand edits made
 * to that block since the last run - only hand-edit right before merging
 * the Release PR, not mid-review.
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

	// commitlint's subject-full-stop rule already guarantees a subject never
	// ends in a period, but nothing lints a `Release-Note:` trailer, so the
	// two paths can disagree. Normalize the end of the text the same way
	// ucfirst() normalizes the start. The lookbehind leaves an ellipsis alone.
	$text = preg_replace( '/(?<!\.)\.\z/', '', $text );

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

$head          = substr( $readme, 0, $pos + strlen( $marker ) );
$changelog_raw = ltrim( substr( $readme, $pos + strlen( $marker ) ), "\n" );

// Split on version headings and drop any existing block for this version,
// so re-running the script replaces it instead of appending a duplicate -
// splitting on heading lines (rather than matching bullet lines) handles
// hand-edited bullets that wrap onto a continuation line too.
$blocks = preg_split( '/(?=^= .+ =$)/m', $changelog_raw );
$blocks = array_filter(
	$blocks,
	static function ( $block ) use ( $version ) {
		return 0 !== strpos( ltrim( $block ), "= {$version} =" );
	}
);

$readme = $head . "\n\n" . $entry . "\n\n" . implode( '', $blocks );

file_put_contents( $readme_path, $readme );

fwrite( STDOUT, "Drafted changelog entry for {$version}:\n\n{$entry}\n" );
