import gulp from 'gulp'
import debug from 'gulp-debug'
import sass from 'gulp-sass'
import less from 'gulp-less'
import rename from 'gulp-rename'
import gulpIgnore from 'gulp-ignore'
import gulpif from 'gulp-if'
import cached from 'gulp-cached'
import dependents from 'gulp-dependents'
import sourcemaps from 'gulp-sourcemaps'
import cleanCSS from 'gulp-clean-css'
import autoprefixer from 'gulp-autoprefixer'
import plumber from 'gulp-plumber'
import notify from 'gulp-notify'
import sassDataURI from 'lib-sass-data-uri'
import replace from 'gulp-replace'
import log from './log'
import path from 'path'
import {paths, basePath} from '../paths'

export function themesStyles() {
    return gulp.src(`${basePath}uConnect*/css/**/*.scss`, {
        base: basePath,
        allowEmpty: true
    })
        // prevent caching to force all the files compile for production
        .pipe(gulpif(process.env.NODE_ENV === 'development', cached('themes_sass')))
        // Do not break tasks and gulp in case of error. Just show a notification.
        .pipe(gulpif(process.env.NODE_ENV === 'development', plumber({errorHandler: notify.onError("Error in sass build.")})))
        // find all the files dependent to the current processing file and add them to the pipeline.
        .pipe(dependents())
        // exclude dependencies and only keep files should be compiled
        .pipe(gulpIgnore.include(paths.themes.styles.src.map(src => `uConnect*/${src}`)))
        // initial Sourcemap to be able to find the source of css easily in the browser developer tool
        .pipe(sourcemaps.init())
        // change data-uri path to absolute (sassDataURI doesn't work with relative path. It use cwd path as base.)
        .pipe(replace(/data-url\(\s*["|']/gm, function(match, p1, offset, string) {
            const scss_file_path = path.parse(this.file.path)
            return `${match}${scss_file_path.dir}/`;
        }))
        // compile scss to css
        .pipe(sass({functions: Object.assign(sassDataURI, {other: function() {}})}))
        // convert THEME_CDN_URL to CDN url
        .pipe(replace('THEME_CDN_URL', function(match, p1, offset, string){
            const regex = /(?:themes\/)(.*)(?:\/css)/s;
            let themeName = 'uConnect'
            const m = regex.exec(this.file.path)
            if (m !== null) themeName = m[1]
            return `https://cdn.uconnectlabs.${ (process.env.NODE_ENV === 'development' || process.env.NODE_ENV ==='development_build'?'test': 'com')}/wp-content/themes/${themeName}`
        }))
        // add browser specific prefixes to css (e.g. -moz, -webkit)
        .pipe(autoprefixer())
        // clean comments and minify css
        .pipe(gulpif(process.env.NODE_ENV === 'production', cleanCSS({level: 2})))
        // prepend theme name to the destination path. Also rename style.scss files to the name of their parent folder (module name).
        .pipe(rename(function (path) {
            const pathSplit = path.dirname.split('/')
            const themeName = pathSplit[0]
            // Set file name equal to module folder name
            if (path.dirname.includes('modules')) {
                path.basename = pathSplit[pathSplit.length -1]
            }

            path.dirname = `${themeName}/${paths.themes.styles.dest}`

            if (process.env.NODE_ENV === 'production') path.extname = `.min${path.extname}`
        }))
        .pipe(debug({title: 'compiled:'}))
        // write Sourcemap
        .pipe(sourcemaps.write('.'))
        // write generate .css to destination
        .pipe(gulp.dest(basePath))
        // write version of file into /themes/css_versions.php (versions are md5 of file contents)
        .pipe(gulpif(process.env.NODE_ENV === 'production', log('css_versions')));
}




// This if for old less files in the plugins folder. Should be removed when all the modules less files moved to the theme and converted to sass.
export function deprecatedStyles(file) {
    return gulp.src((typeof (file) === 'string') ? file : paths.deprecated.styles.src, {base: "./"})
        .pipe(less())
        // add browser specific prefixes to css (e.g. -moz, -webkit)
        .pipe(autoprefixer())
        // clean comments and minify css
        .pipe(cleanCSS({level: 2}))
        // write generate .css to destination
        .pipe(gulp.dest(paths.deprecated.styles.dest))
        // write version of file into /themes/plugins_css_versions.php (versions are md5 of file contents)
        .pipe(gulpif(process.env.NODE_ENV === 'production',log('deprecated_css_versions')));
}