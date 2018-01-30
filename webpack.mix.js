const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */


// mix.js('resources/assets/js/app.js', 'public/js');
    mix.sass('resources/assets/sass/bulma/bulma.scss', 'public/css/bulma.css').version();

    // mix.less('resources/assets/adminlte/less/AdminLTE.less', 'public/css/alte.css');

