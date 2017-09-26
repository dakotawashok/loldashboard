const { mix } = require('laravel-mix');
var path = require('path');

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

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('resources/assets/js/jquery.min.js', 'public/js/jquery.min.js')
    .copy('semantic/dist/semantic.css','public/css/semantic.css')
    .copy('semantic/dist/semantic.js','public/js/semantic.js');

mix.webpackConfig({
    resolve: {
        modules: [
            path.resolve(__dirname, 'semantic/dist'),
            path.resolve(__dirname, 'node_modules')
        ],
        extensions: ['*', '.js', '.jsx', '.vue'],

        alias: {
            'vue$': 'vue/dist/vue.common.js'
        }
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [ 'style-loader', 'css-loader' ]
            }
        ]
    }
});
