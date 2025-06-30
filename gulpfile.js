'use strict';

const {src, dest, parallel, series, watch} = require('gulp');
const del = require('del');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const sourcemaps = require('gulp-sourcemaps');
const util = require('gulp-util');
const browserSync = require('browser-sync').create();
const babel = require('gulp-babel');
const jsonminify = require('gulp-jsonminify');

var config = {
    production: !!util.env.production
};

function clean() {
    return del(['./public/js/**/*.*', './public/css/**/*.*']);
}

function css() {
    return src([
        './src/Resources/css/font.scss',
        './node_modules/bootstrap/dist/css/bootstrap.css',
        './node_modules/metismenu/dist/metisMenu.css',
        './node_modules/bootstrap-select/dist/css/bootstrap-select.css',
        './node_modules/ajax-bootstrap-select/dist/css/ajax-bootstrap-select.css',
        './node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
        './node_modules/@yaireo/tagify/dist/tagify.css',
        './src/Resources/css/tag.scss',
        './src/Resources/css/font-awesome/fontawesome.scss',
        './src/Resources/css/font-awesome/solid.scss',
        './src/Resources/css/font-awesome/regular.scss',
        './src/Resources/css/index.scss',
        './src/Resources/css/loader.scss',
        './src/Resources/css/switch.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssPublic() {
    return src([
        './src/Resources/css/font.scss',
        './node_modules/bootstrap/dist/css/bootstrap.css',
        './node_modules/bootstrap-select/dist/css/bootstrap-select.css',
        './src/Resources/css/font-awesome/fontawesome.scss',
        './src/Resources/css/font-awesome/solid.scss',
        './src/Resources/css/font-awesome/regular.scss',
        './src/Resources/css/index-public.scss',
        './src/Resources/css/switch.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab-public.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssError() {
    return src([
        './src/Resources/css/font.scss',
        './node_modules/bootstrap/dist/css/bootstrap.css',
        './src/Resources/css/error.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab.error.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssTinymce() {
    return src('./src/Resources/css/tinymce.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab.tinymce.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssEmail() {
    return src([
        './node_modules/foundation-emails/scss/foundation-emails.scss',
        './src/Resources/css/email.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab.email.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssMap() {
    return src([
        'node_modules/leaflet/dist/leaflet.css',
        'node_modules/leaflet.markercluster/dist/MarkerCluster.css',
        'node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css',
        'src/Resources/css/map.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            // sass.logError
            this.emit("end");
        }))
        .pipe(concat('alab.map.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function cssFullcalendar() {
    return src([
        './node_modules/fullcalendar/main.css',
        './src/Resources/css/fullcalendar.scss'
    ])
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', function () {
            console.log(err.message);
            this.emit("end");
        }))
        .pipe(concat('alab.fullcalendar.min.css'))
        .pipe(config.production ? cleanCSS({compatibility: 'ie8'}) : util.noop())
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/css'))
        .pipe(browserSync.stream());
}

function js() {
    return src([
        './node_modules/jquery/dist/jquery.js',
        './src/Resources/js/jquery-ui.custom.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './node_modules/metismenu/dist/metisMenu.js',
        './node_modules/bootstrap-select/dist/js/bootstrap-select.js',
        './node_modules/ajax-bootstrap-select/dist/js/ajax-bootstrap-select.js',
        './node_modules/moment/moment.js',
        './node_modules/moment/locale/pl.js',
        './node_modules/moment/locale/en-gb.js',
        './node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        './node_modules/bootstrap-notify/bootstrap-notify.js',
        './node_modules/@yaireo/tagify/dist/tagify.js',
        './src/Resources/js/render.js',
        './src/Resources/js/notification.js',
        './node_modules/floatthead/dist/jquery.floatThead.js',
        './src/Resources/js/index.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsPublic() {
    return src([
        './node_modules/jquery/dist/jquery.js',
        './src/Resources/js/jquery-ui.custom.js',
        './node_modules/bootstrap-select/dist/js/bootstrap-select.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './src/Resources/js/index-public.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab-public.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsMap() {
    return src([
        'node_modules/leaflet/dist/leaflet.js',
        'node_modules/leaflet.markercluster/dist/leaflet.markercluster.js',
        'src/Resources/js/map.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.map.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsPhone() {
    return src('./src/Resources/js/phone.js')
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.phone.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsNestedSortable() {
    return src('./src/Resources/js/jquery.mjs.nestedSortable.js')
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.nestedSortable.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsTinymce() {
    return src('./src/Resources/js/tinymce.js')
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.tinymce.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsPwstrength() {
    return src([
        './node_modules/i18next/i18next.js',
        './node_modules/pwstrength-bootstrap/dist/pwstrength-bootstrap.js',
        './node_modules/password-generator/dist/password-generator.js',
        './src/Resources/js/pwstrength.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.pwstrength.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsFullcalendar() {
    return src([
        './node_modules/fullcalendar/main.js',
        './node_modules/fullcalendar-interaction/main.global.min.js',
        './node_modules/fullcalendar/locales/pl.js',
        './node_modules/fullcalendar/locales/en-gb.js',
        './src/Resources/js/fullcalendar.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.fullcalendar.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsFileSection() {
    return src([
        './src/Resources/js/fileSection.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.fileSection.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function jsFile() {
    return src([
        './src/Resources/js/file.js'
    ])
        .pipe(babel({
            presets: [
                ['@babel/env', {
                    modules: false
                }]
            ]
        }))
        .pipe(sourcemaps.init())
        .pipe(config.production ? uglify() : util.noop())
        .pipe(concat('alab.file.min.js'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('public/js'))
        .pipe(browserSync.stream());
}

function fonts() {
    return src('./node_modules/bootstrap/fonts/*', {base: './node_modules/bootstrap/fonts'})
        .pipe(dest('./public/fonts'));
}

function jsTranslations() {
    return src('./src/Resources/js/translations/**/*', {base: './src/Resources/js/translations'})
        .pipe(jsonminify())
        .pipe(dest('./public/js/translations'));
}

function jsTemplates() {
    return src('./src/Resources/js/template/**/*', {base: './src/Resources/js/template'})
        .pipe(dest('./public/js/template'));
}

function jsTinymceModules() {
    return src('./node_modules/tinymce/**/*', {base: './node_modules/tinymce'})
        .pipe(dest('./public/js/tinymce'));
}

function jsTinymceTranslations() {
    return src('./src/Resources/js/tinymce/**/*', {base: './src/Resources/js/tinymce'})
        .pipe(dest('./public/js/tinymce'));
}

function watchFiles(done) {
    browserSync.init({
        open: false,
        host: 'intranet.alab',
        proxy: "intranet.alab"
    });

    watch('./src/Resources/css/index.scss', css);
    watch('./src/Resources/css/index-public.scss', cssPublic);
    watch('./src/Resources/css/loader.scss', css);
    watch('./src/Resources/css/switch.scss', css);
    watch('./src/Resources/css/error.scss', cssError);
    watch('./src/Resources/css/tinymce.scss', cssTinymce);
    watch('./src/Resources/css/fullcalendar.scss', cssFullcalendar);
    watch('./src/Resources/css/email.scss', cssEmail);
    watch('./src/Resources/css/map.scss', cssMap);

    watch('./src/Resources/js/index.js', js);
    watch('./src/Resources/js/index-public.js', jsPublic);
    watch('./src/Resources/js/render.js', js);
    watch('./src/Resources/js/notification.js', js);
    watch('./src/Resources/js/index.js', js);
    watch('./src/Resources/js/tinymce.js', jsTinymce);
    watch('./src/Resources/js/pwstrength.js', jsPwstrength);
    watch('./src/Resources/js/fullcalendar.js', jsFullcalendar);
    watch('./src/Resources/js/map.js', jsMap);
    watch('./src/Resources/js/fileSection.js', jsFileSection);
    watch('./src/Resources/js/file.js', jsFile);
    watch('./src/Resources/js/phone.js', jsPhone);

    done()
}

function buildCss() {
    return parallel(css, cssError, cssTinymce, cssFullcalendar, cssEmail, cssMap, cssPublic, fonts);
}

function buildJs() {
    return parallel(js, jsNestedSortable, jsTinymce, jsPwstrength, jsTranslations, jsTemplates, jsTinymceModules, jsTinymceTranslations, jsFullcalendar, jsMap, jsFileSection, jsFile, jsPhone, jsPublic);
}

exports.build = series(
    clean,
    buildCss(),
    buildJs()
);

exports.default = series(
    clean,
    buildCss(),
    buildJs(),
    watchFiles
);
