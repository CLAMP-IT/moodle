// List of modules used.
var gulp    = require('gulp'),
    bump    = require('gulp-bump'),     // Generates new version.
    argv    = require('yargs')
        .default('release', 'patch')
        .argv,                          // CLI parser.
    fs      = require('fs'),            // Used by bump.
    semver  = require('semver'),        // Used by bump.
    git     = require('gulp-git'),      // Git wrapper.
    jshint  = require('gulp-jshint'),   // Lints JS.
    phplint = require('phplint'),       // Lints PHP.
    phpcs   = require('gulp-phpcs'),    // Moodle standards.
    less    = require('gulp-less'),     // Compile less into CSS.
    csslint = require('gulp-csslint'),  // Lint CSS.
    replace = require('gulp-replace');  // Text replacer.

// Paths.
var paths = {
  scripts: ['./yui/roster/*.js'],
  styles: ['./less/styles.less'],
  php: ['./*.php', './db/**/*.php', './lang/**/*.php']
};

// Parses the package.json file. We use this because its values
// change during execution.
var getPackageJSON = function() {
  return JSON.parse(fs.readFileSync('./package.json', 'utf8'));
};

// Compile and lint CSS.
gulp.task('styles', function() {
  gulp.src(paths.styles)
    .pipe(less())
    .pipe(csslint({
    }))
    .pipe(csslint.reporter())
    .pipe(gulp.dest('./'));
});

// Moodle coding standards.
gulp.task('standards', function() {
  gulp.src(paths.php)
    .pipe(phpcs({
      standard: 'moodle'
    }))
    .pipe(phpcs.reporter('log'));
});

// Lint associated PHP files.
gulp.task('phplint', function() {
  return phplint(paths.php);
});

// Lint associated Javascripts.
gulp.task('scripts', function() {
  gulp.src('./gulpfile.js')
  .pipe(jshint())
  .pipe(jshint.reporter('default'))
  .pipe(gulp.dest('./'));
  gulp.src(paths.scripts)
  .pipe(jshint())
  .pipe(jshint.reporter('default'))
  .pipe(gulp.dest('./yui/roster'));
});

// Integration task. Bumps version and commits.
// Tagging is separate.
gulp.task('integrate', function() {
  var pkg = getPackageJSON();
  var newversion = semver.inc(pkg.version, argv.release);

  gulp.src('./package.json')
  .pipe(bump({version: newversion}))
  .pipe(gulp.dest('./'));

  gulp.src(['./version.php'])
  .pipe(replace(pkg.version, newversion))
  .pipe(gulp.dest('./'));

  gulp.src(['package.json','version.php'])
  .pipe(git.commit(pkg.description + ' v' + newversion, {cwd: './'}));
});

// Tags. Run this after integrating.
gulp.task('tag', function() {
  var pkg = getPackageJSON();
  git.tag('v'+pkg.version, pkg.description + ' v' + pkg.version, function(err) {
  });
});

// Watch.
gulp.task('watch', function() {
  gulp.watch(paths.scripts, ['scripts']);
  gulp.watch(paths.php, ['standards', 'phplint']);
  gulp.watch(paths.styles, ['styles']);
});

// Default task.
gulp.task('default', ['watch', 'scripts', 'standards', 'phplint', 'styles']);
