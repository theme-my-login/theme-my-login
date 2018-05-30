var gulp = require('gulp'),
	sass = require('gulp-sass'),
	clean = require('gulp-clean'),
	concat = require('gulp-concat'),
	rename = require('gulp-rename'),
	uglify = require('gulp-uglify'),
	cleanCSS = require('gulp-clean-css'),
	imagemin = require('gulp-imagemin'),
	runSequence = require('run-sequence'),
	autoprefixer = require('gulp-autoprefixer');

// Copy
gulp.task('copy', function() {
	return gulp.src([
		'src/**',
		'!src/assets/**/*',
		'!src/admin/assets/**/*'
	])
	.pipe(gulp.dest('build'));
});

// Styles
gulp.task('styles', function() {
	return gulp.src([
		'src/assets/styles/**/*.scss'
	])
	.pipe(sass({
		includePaths: [
			'node_modules'
		],
		indentType: 'tab',
		indentWidth: 1,
		outputStyle: 'expanded'
	}))
	.pipe(autoprefixer())
	.pipe(gulp.dest('build/assets/styles'))
	.pipe(cleanCSS())
	.pipe(rename({
		extname: '.min.css'
	}))
	.pipe(gulp.dest('build/assets/styles'));
});

// Admin styles
gulp.task('admin-styles', function() {
	return gulp.src([
		'src/admin/assets/styles/**/*.scss'
	])
	.pipe(sass({
		includePaths: [
			'node_modules'
		],
		indentType: 'tab',
		indentWidth: 1,
		outputStyle: 'expanded'
	}))
	.pipe(autoprefixer())
	.pipe(gulp.dest('build/admin/assets/styles'))
	.pipe(cleanCSS())
	.pipe(rename({
		extname: '.min.css'
	}))
	.pipe(gulp.dest('build/admin/assets/styles'));
});

// Scripts
gulp.task('scripts', function() {
	return gulp.src([
		'src/assets/scripts/*.js'
	])
	.pipe(concat('theme-my-login.js'))
	.pipe(gulp.dest('build/assets/scripts'))
	.pipe(uglify())
	.pipe(rename({
		extname: '.min.js'
	}))
	.pipe(gulp.dest('build/assets/scripts'));
});

// Admin scripts
gulp.task('admin-scripts', function() {
	return gulp.src([
		'src/admin/assets/scripts/*.js'
	])
	.pipe(concat('theme-my-login-admin.js'))
	.pipe(gulp.dest('build/admin/assets/scripts'))
	.pipe(uglify())
	.pipe(rename({
		extname: '.min.js'
	}))
	.pipe(gulp.dest('build/admin/assets/scripts'));
});

// Images
gulp.task('images', function() {
	return gulp.src([
		'src/assets/images/**/*'
	])
	.pipe(imagemin([
		imagemin.gifsicle({interlaced: true}),
		imagemin.jpegtran({progressive: true}),
		imagemin.svgo({
			plugins: [
				{removeViewBox: true},
				{cleanupIDs: false},
			]
		})
	]))
	.pipe(gulp.dest('build/assets/images'));
});

// Admin images
gulp.task('admin-images', function() {
	return gulp.src([
		'src/admin/assets/images/**/*'
	])
	.pipe(imagemin([
		imagemin.gifsicle({interlaced: true}),
		imagemin.jpegtran({progressive: true}),
		imagemin.svgo({
			plugins: [
				{removeViewBox: true},
				{cleanupIDs: false},
			]
		})
	]))
	.pipe(gulp.dest('build/admin/assets/images'));
});

// Watch
gulp.task('watch', function() {
	// Assets
	gulp.watch('src/assets/styles/**/*.scss', ['styles']);
	gulp.watch('src/assets/scripts/*.js', ['scripts']);
	gulp.watch('src/assets/images/**/*', ['images']);

	// Admin assets
	gulp.watch('src/admin/assets/styles/**/*.scss', ['admin-styles']);
	gulp.watch('src/admin/assets/scripts/*.js', ['admin-scripts']);
	gulp.watch('src/admin/assets/images/**/*', ['admin-images']);

	// All other files
	gulp.watch(['src/**/*', '!src/assets/**/*', '!src/admin/assets/**/*'], function(obj) {
		if (obj.type === 'changed') {
			return gulp.src(obj.path, {
				base: 'src/',
			})
			.pipe(gulp.dest('build'));
		}
	});
});

// Clean
gulp.task('clean', function() {
	return gulp.src('build', {
		read: false
	})
	.pipe(clean());
});

// Build
gulp.task('build', function(callback) {
	runSequence('clean', 'copy',
		['styles', 'scripts', 'images'],
		['admin-styles', 'admin-scripts', 'admin-images'],
		callback
	);
});

// Default
gulp.task('default', ['build']);
