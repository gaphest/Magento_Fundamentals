var gulp = require('gulp'),
    compass = require('gulp-compass'),
//    gutil = require('gulp-util'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
//    uglify = require('gulp-uglify'),
    changed = require('gulp-changed'),
    watch = require('gulp-watch'),
    imagemin = require('gulp-imagemin'),
    jshint = require('gulp-jshint'),
    foreach = require('gulp-foreach'),
//    gulpif = require('gulp-if'),
    argv = require('yargs').argv,

    CSS_DIR = 'astrio/theme/css',
    JS_DIR = 'astrio/theme/js';

// Clean all build files
gulp.task('clean', function() {
    gulp.src([
            CSS_DIR + '/*'
        ])
        .pipe(clean());
});


// Check theme's js files
gulp.task('check-js', function() {
    return gulp.src([
            JS_DIR + '/*.js'
        ])
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

// Compiles css from scss files
// TODO configure gulp-changed
gulp.task('compass', function() {
    gulp.src([
            'scss/styles.scss',
            'scss/**/*.scss'
        ])
        .pipe(changed(CSS_DIR))
        .pipe(compass({
            config_file: 'config.rb',
            css: CSS_DIR,
            sass: 'scss'
        }))
        .pipe(gulp.dest(CSS_DIR));
});

// Optimize skin images
// TODO to make it work with nested folders
gulp.task('images', function() {
    return gulp.src([
            'images/*',
            '!images/media',
            '!images/icons'
        ])
        .pipe(imagemin({
            progressive: true
        }))
        .pipe(clean())
        .pipe(gulp.dest('images'));
});

// Gulp watches
gulp.task('watch', function() {
    gulp.watch(
        ['scss/styles.scss', 'scss/**/*.scss'], ['compass']
    );
    gulp.watch(
        [ JS_DIR + '/*.js' ], ['check-js']
    );
});

// Run all
gulp.task('default', ['clean', 'compass']);