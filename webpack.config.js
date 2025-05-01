const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addStyleEntry('auth', './assets/styles/auth.css')
    .copyFiles([{
        from: './assets/images',
        to: 'images/[path][name].[ext]'
    }, {
        from: './images',
        to: 'images/[path][name].[ext]'
    }])
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-transform-class-properties');
    })
;

module.exports = Encore.getWebpackConfig();