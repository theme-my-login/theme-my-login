var gulp = require('gulp'),
	gulpSass = require('gulp-sass'),
	gulpClean = require('gulp-clean'),
	gulpConcat = require('gulp-concat'),
	gulpRename = require('gulp-rename'),
	gulpUglify = require('gulp-uglify'),
	gulpCleanCSS = require('gulp-clean-css'),
	gulpImagemin = require('gulp-imagemin'),
	gulpAutoprefixer = require('gulp-autoprefixer');

// Clean
function clean() {
	return gulp.src('build', {
		allowEmpty: true,
		read: false
	})
	.pipe(gulpClean());
}

// Copy
function copy() {
	return gulp.src([
		'src/**',
		'!src/assets/**/*',
		'!src/admin/assets/**/*'
	])
	.pipe(gulp.dest('build'));
}

// Styles
function styles() {
	return gulp.src([
		'src/assets/styles/**/*.scss'
	])
	.pipe(gulpSass({
		includePaths: [
			'node_modules'
		],
		indentType: 'tab',
		indentWidth: 1,
		outputStyle: 'expanded'
	}))
	.pipe(gulpAutoprefixer())
	.pipe(gulp.dest('build/assets/styles'))
	.pipe(gulpCleanCSS())
	.pipe(gulpRename({
		extname: '.min.css'
	}))
	.pipe(gulp.dest('build/assets/styles'));
}

// Admin styles
function adminStyles() {
	return gulp.src([
		'src/admin/assets/styles/**/*.scss'
	])
	.pipe(gulpSass({
		includePaths: [
			'node_modules'
		],
		indentType: 'tab',
		indentWidth: 1,
		outputStyle: 'expanded'
	}))
	.pipe(gulpAutoprefixer())
	.pipe(gulp.dest('build/admin/assets/styles'))
	.pipe(gulpCleanCSS())
	.pipe(gulpRename({
		extname: '.min.css'
	}))
	.pipe(gulp.dest('build/admin/assets/styles'));
}

// Scripts
function scripts() {
	return gulp.src([
		'src/assets/scripts/*.js'
	])
	.pipe(gulpConcat('theme-my-login.js'))
	.pipe(gulp.dest('build/assets/scripts'))
	.pipe(gulpUglify())
	.pipe(gulpRename({
		extname: '.min.js'
	}))
	.pipe(gulp.dest('build/assets/scripts'));
}

// Admin scripts
function adminScripts() {
	return gulp.src([
		'src/admin/assets/scripts/*.js'
	])
	.pipe(gulpConcat('theme-my-login-admin.js'))
	.pipe(gulp.dest('build/admin/assets/scripts'))
	.pipe(gulpUglify())
	.pipe(gulpRename({
		extname: '.min.js'
	}))
	.pipe(gulp.dest('build/admin/assets/scripts'));
}

// Images
function images() {
	return gulp.src([
		'src/assets/images/**/*'
	])
	.pipe(gulpImagemin([
		gulpImagemin.gifsicle({interlaced: true}),
		gulpImagemin.mozjpeg({quality: 75, progressive: true}),
		gulpImagemin.optipng({optimizationLevel: 5}),
		gulpImagemin.svgo({
			plugins: [
				{removeViewBox: true},
				{cleanupIDs: false}
			]
		})
	]))
	.pipe(gulp.dest('build/assets/images'));
}

// Admin images
function adminImages() {
	return gulp.src([
		'src/admin/assets/images/**/*'
	])
	.pipe(gulpImagemin([
		gulpImagemin.gifsicle({interlaced: true}),
		gulpImagemin.mozjpeg({quality: 75, progressive: true}),
		gulpImagemin.optipng({optimizationLevel: 5}),
		gulpImagemin.svgo({
			plugins: [
				{removeViewBox: true},
				{cleanupIDs: false}
			]
		})
	]))
	.pipe(gulp.dest('build/admin/assets/images'));
}

// Watch
exports.watch = function() {
	// Assets
	gulp.watch('src/assets/styles/**/*.scss', {usePolling: true}, styles);
	gulp.watch('src/assets/scripts/*.js', {usePolling: true}, scripts);
	gulp.watch('src/assets/images/**/*', {usePolling: true}, images);

	// Admin assets
	gulp.watch('src/admin/assets/styles/**/*.scss', {usePolling: true}, adminStyles);
	gulp.watch('src/admin/assets/scripts/*.js', {usePolling: true}, adminScripts);
	gulp.watch('src/admin/assets/images/**/*', {usePolling: true}, adminImages);

	// All other files
	gulp.watch(['src/**/*', '!src/assets/**/*', '!src/admin/assets/**/*'], {usePolling: true}, function(obj) {
		if (obj.type === 'changed') {
			return gulp.src(obj.path, {
				base: 'src/',
			})
			.pipe(gulp.dest('build'));
		}
	});
}

// Default (Build)
exports.default = gulp.series(
	clean,
	copy,
	gulp.parallel(styles, scripts, images),
	gulp.parallel(adminStyles, adminScripts, adminImages)
);
