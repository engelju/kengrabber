/**
 * Copyright 2015 Simon Erhardt <me@rootlogin.ch>
 *
 * This file is part of kg.
 * kg is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * kg is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with kg.
 * If not, see http://www.gnu.org/licenses/.
 */
var gulp = require('gulp'),
    less = require('gulp-less'),
    watch = require('gulp-watch'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    livereload = require('gulp-livereload'),
    LessPluginCleanCSS = require('less-plugin-clean-css'),
    LessPluginAutoPrefix = require('less-plugin-autoprefix'),
    cleancss = new LessPluginCleanCSS({ advanced: true }),
    autoprefix = new LessPluginAutoPrefix({ browsers: ['last 2 version', 'ie 8', 'ie 9', 'ios 6', 'android 4'] }),
    minifyCSS = require('gulp-minify-css'),
    run = require('gulp-run');


var config = {
    "bowerDir": "./bower_components"
};

gulp.task('fonts', function () {
    gulp.src(config.bowerDir + '/bootstrap/fonts/**.*')
        .pipe(gulp.dest('./app/dist/web/fonts/'));
});

gulp.task('css', function () {
    gulp.src('./app/dist/less/style.less')
        .pipe(less({
            paths: [
                './app/dist/less/',
                config.bowerDir + '/bootstrap/less/'
            ],
            plugins: [autoprefix, cleancss]
        }))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./app/dist/web/res'))
        .pipe(livereload({ auto: false }));
});

gulp.task('js', function () {
    gulp.src([
        config.bowerDir + '/jquery/dist/jquery.js',
        config.bowerDir + '/bootstrap/dist/js/bootstrap.js',
        config.bowerDir + '/angular/angular.js',
        config.bowerDir + '/angular-route/angular-route.js',
        config.bowerDir + '/angular-audio/app/angular.audio.js',
        config.bowerDir + '/angular-ui-bootstrap-bower/ui-bootstrap.js',
        config.bowerDir + '/angular-ui-bootstrap-bower/ui-bootstrap-tpls.js',
        './app/dist/js/app.js'])
        .pipe(concat('app.min.js'))
        //.pipe(uglify())
        .pipe(gulp.dest('./app/dist/web/res'))
        .pipe(livereload({ auto: false }));
});

gulp.task('render', function() {
    run('php kengrabber.php render').exec()
        .pipe(livereload({ auto: false }));
});

gulp.task('watch', function() {
    livereload.listen();

    gulp.start(['default']);

    gulp.watch('./app/dist/less/*.less', ['css','render']);
    gulp.watch('./app/dist/js/*.js', ['js','render']);

    gulp.watch('./app/dist/web/*.*', ['render']);
    gulp.watch('./src/**.*', ['render']);
});

gulp.task('default', ['fonts', 'css', 'js','render']);