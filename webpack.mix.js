const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false
    });

mix.webpackConfig({
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.common.js',
            '@': path.resolve(__dirname, 'resources/js')
        }
    }
});