#!/usr/bin/env node

/**
 * Build script for the plugin's front-end assets. Replaces the old
 * gulpfile.js with plain esbuild/sass/postcss/svgo, invoked via
 * `npm run build` / `npm run watch` / `npm run clean`.
 */

import { cp, mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises';
import { dirname, join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

import * as esbuild from 'esbuild';
import * as sass from 'sass';
import postcss from 'postcss';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import { optimize } from 'svgo';

const ROOT = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const SRC = join( ROOT, 'src' );
const BUILD = join( ROOT, 'build' );

const prefix = postcss( [ autoprefixer ] );
const minify = postcss( [ cssnano ] );

async function clean() {
	await rm( BUILD, { recursive: true, force: true } );
}

// Copies everything under src/ except the assets/ subtrees, which are
// handled by the styles/scripts/images tasks below.
async function copy() {
	await cp( SRC, BUILD, {
		recursive: true,
		filter: ( source ) => {
			const rel = relative( SRC, source );
			return rel !== 'assets' && rel !== join( 'admin', 'assets' )
				&& ! rel.startsWith( `assets${ sep }` )
				&& ! rel.startsWith( `admin${ sep }assets${ sep }` );
		},
	} );
}

async function compileStyles( entry, outDir, outName ) {
	const { css } = sass.compile( entry, {
		loadPaths: [ join( ROOT, 'node_modules' ) ],
		style: 'expanded',
	} );

	await mkdir( outDir, { recursive: true } );

	const prefixed = await prefix.process( css, { from: undefined } );
	await writeFile( join( outDir, `${ outName }.css` ), prefixed.css );

	const minified = await minify.process( prefixed.css, { from: undefined } );
	await writeFile( join( outDir, `${ outName }.min.css` ), minified.css );
}

function styles() {
	return compileStyles(
		join( SRC, 'assets/styles/theme-my-login.scss' ),
		join( BUILD, 'assets/styles' ),
		'theme-my-login'
	);
}

function adminStyles() {
	return compileStyles(
		join( SRC, 'admin/assets/styles/theme-my-login-admin.scss' ),
		join( BUILD, 'admin/assets/styles' ),
		'theme-my-login-admin'
	);
}

async function compileScripts( srcDir, outDir, outName ) {
	let files;

	try {
		files = ( await readdir( srcDir ) ).filter( ( f ) => f.endsWith( '.js' ) ).sort();
	} catch ( err ) {
		if ( err.code === 'ENOENT' ) return;
		throw err;
	}

	const contents = await Promise.all(
		files.map( ( f ) => readFile( join( srcDir, f ), 'utf8' ) )
	);
	const concatenated = contents.join( '\n' );

	await mkdir( outDir, { recursive: true } );
	await writeFile( join( outDir, `${ outName }.js` ), concatenated );

	const minified = await esbuild.transform( concatenated, { minify: true, loader: 'js' } );
	await writeFile( join( outDir, `${ outName }.min.js` ), minified.code );
}

function scripts() {
	return compileScripts(
		join( SRC, 'assets/scripts' ),
		join( BUILD, 'assets/scripts' ),
		'theme-my-login'
	);
}

function adminScripts() {
	return compileScripts(
		join( SRC, 'admin/assets/scripts' ),
		join( BUILD, 'admin/assets/scripts' ),
		'theme-my-login-admin'
	);
}

// Optimizes SVGs with svgo; other image types are copied through as-is
// (there's currently no raster-image pipeline to modernize/replace).
async function compileImages( srcDir, outDir ) {
	let entries;

	try {
		entries = await readdir( srcDir, { recursive: true, withFileTypes: true } );
	} catch ( err ) {
		if ( err.code === 'ENOENT' ) return;
		throw err;
	}

	await Promise.all( entries.filter( ( e ) => e.isFile() ).map( async ( entry ) => {
		const from = join( entry.parentPath, entry.name );
		const rel = relative( srcDir, from );
		const to = join( outDir, rel );

		await mkdir( dirname( to ), { recursive: true } );

		if ( entry.name.endsWith( '.svg' ) ) {
			const svg = await readFile( from, 'utf8' );
			const optimized = optimize( svg, {
				path: from,
				plugins: [
					{
						name: 'preset-default',
						params: {
							overrides: {
								cleanupIds: false,
							},
						},
					},
				],
			} );
			await writeFile( to, optimized.data );
		} else {
			await cp( from, to );
		}
	} ) );
}

function images() {
	return compileImages( join( SRC, 'assets/images' ), join( BUILD, 'assets/images' ) );
}

function adminImages() {
	return compileImages( join( SRC, 'admin/assets/images' ), join( BUILD, 'admin/assets/images' ) );
}

async function build() {
	await clean();
	await copy();
	await Promise.all( [ styles(), scripts(), images() ] );
	await Promise.all( [ adminStyles(), adminScripts(), adminImages() ] );
}

async function watch() {
	const { default: chokidar } = await import( 'chokidar' );

	await build();

	const run = ( label, task ) => () => {
		task().catch( ( err ) => console.error( `${ label } failed:`, err ) );
	};

	// chokidar 4 dropped glob support, so each asset subtree gets its own
	// directory watcher instead of a `**/*.ext` pattern.
	const assetsDir = join( SRC, 'assets' );
	const adminAssetsDir = join( SRC, 'admin/assets' );

	chokidar.watch( join( assetsDir, 'styles' ), { ignoreInitial: true } ).on( 'all', run( 'styles', styles ) );
	chokidar.watch( join( assetsDir, 'scripts' ), { ignoreInitial: true } ).on( 'all', run( 'scripts', scripts ) );
	chokidar.watch( join( assetsDir, 'images' ), { ignoreInitial: true } ).on( 'all', run( 'images', images ) );

	chokidar.watch( join( adminAssetsDir, 'styles' ), { ignoreInitial: true } ).on( 'all', run( 'admin styles', adminStyles ) );
	chokidar.watch( join( adminAssetsDir, 'scripts' ), { ignoreInitial: true } ).on( 'all', run( 'admin scripts', adminScripts ) );
	chokidar.watch( join( adminAssetsDir, 'images' ), { ignoreInitial: true } ).on( 'all', run( 'admin images', adminImages ) );

	chokidar.watch( SRC, {
		ignored: ( path ) => path === assetsDir || path === adminAssetsDir
			|| path.startsWith( `${ assetsDir }${ sep }` ) || path.startsWith( `${ adminAssetsDir }${ sep }` ),
		ignoreInitial: true,
	} ).on( 'all', async ( event, file ) => {
		if ( event !== 'add' && event !== 'change' ) return;

		const rel = relative( SRC, file );
		console.log( `Copying '${ rel }'` );
		await mkdir( dirname( join( BUILD, rel ) ), { recursive: true } );
		await cp( file, join( BUILD, rel ) );
	} );

	console.log( 'Watching for changes...' );
}

const args = process.argv.slice( 2 );

if ( args.includes( '--clean' ) ) {
	await clean();
} else if ( args.includes( '--watch' ) ) {
	await watch();
} else {
	await build();
}
