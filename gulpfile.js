var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();

gulp.task('default', function() {

    // procesar SCSS
    gulp.src(['node_modules/select2/dist/css/select2.css', 'web/css/**/*.scss', 'web/css/lato/css/fonts.css', 'web/css/atica.css'])
        .pipe(plugins.sass())
        .pipe(plugins.autoprefixer({
            browsers: [
                'Android 2.3',
                'Android >= 4',
                'Chrome >= 20',
                'Firefox >= 3.6',
                'Explorer >= 8',
                'iOS >= 6',
                'Opera >= 12',
                'Safari >= 6'
            ],
            cascade: false
        }))
        .pipe(plugins.concat('pack.css'))
        .pipe(plugins.cleanCss({
            compability: 'ie8'
        }))
        .pipe(gulp.dest('web/dist/css'));

    // copiar jQuery
    gulp.src('node_modules/jquery/dist/*.min.js')
        .pipe(gulp.dest('web/dist/js/jquery'));

    // copiar Javascript de Bootstrap
    gulp.src('node_modules/bootstrap-sass/assets/javascripts/*.min.js')
        .pipe(gulp.dest('web/dist/js/bootstrap'));

    // copiar Javascript de Select2
    gulp.src('node_modules/select2/dist/js/*')
        .pipe(gulp.dest('web/dist/js/select2'));

    // copiar fuentes
    gulp.src(['node_modules/font-awesome/fonts/*', 'web/css/lato/fonts/**'])
        .pipe(gulp.dest('web/dist/fonts'));
});
