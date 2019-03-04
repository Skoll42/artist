// webpack.config.js
var Encore = require('@symfony/webpack-encore');
    eCoreTemplatePath = './node_modules/ecore-template-skeleton-static/';

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .autoProvidejQuery()
    .enableBuildNotifications()

    .createSharedEntry('vendor', './webpack.shared_entry.js')

    .addEntry('app', [
        './src/AppBundle/Resources/assets/js/app.js',
        './src/AppBundle/Resources/assets/scss/app.scss',
    ])

    .addEntry('main-page', [
        './src/AppBundle/Resources/assets/js/page/main.js',
        './src/AppBundle/Resources/assets/js/module/search.js',

        // css
        './src/AppBundle/Resources/assets/scss/page/main.scss',
    ])

    .addEntry('search-page', [
        './src/AppBundle/Resources/assets/js/page/search.js',
        './src/AppBundle/Resources/assets/js/module/search.js',

        // css
        './src/AppBundle/Resources/assets/scss/page/search.scss',
    ])

    .addEntry('artist-page', [
        './src/AppBundle/Resources/assets/js/page/artist.js',
        './src/AppBundle/Resources/assets/scss/page/artist.scss',
    ])

    .addEntry('profile-edit', [
        './src/AppBundle/Resources/assets/js/module/validation.js',
        './src/AppBundle/Resources/assets/js/module/uploadImage.js',
        './src/AppBundle/Resources/assets/js/profile/edit/profile.js',


        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/edit.scss',
    ])

    .addEntry('profile-settings', [
        './src/AppBundle/Resources/assets/js/module/validation.js',
        './src/AppBundle/Resources/assets/js/profile/edit/settings.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/settings.scss',
    ])

    .addEntry('profile-communication', [
        './src/AppBundle/Resources/assets/js/profile/edit/communication.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/communication.scss',
    ])

    .addEntry('profile-media', [
        './src/AppBundle/Resources/assets/js/profile/edit/media.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/media.scss',
    ])

    .addEntry('profile-booking', [
        './src/AppBundle/Resources/assets/js/profile/edit/booking.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/booking.scss',
    ])

    .addEntry('profile-payment', [
        './src/AppBundle/Resources/assets/js/module/validation.js',
        './src/AppBundle/Resources/assets/js/profile/edit/payment.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/payment.scss',
    ])

    .addEntry('profile-policy', [
        './src/AppBundle/Resources/assets/js/profile/edit/policy.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/policy.scss',
    ])

    .addEntry('customer-profile-edit', [
        './src/AppBundle/Resources/assets/js/module/validation.js',
        './src/AppBundle/Resources/assets/js/module/uploadImage.js',
        './src/AppBundle/Resources/assets/js/profile/edit/customer-profile.js',

        // css
        './src/AppBundle/Resources/assets/scss/profile/edit/profile.scss',
        './src/AppBundle/Resources/assets/scss/profile/edit/edit.scss',
    ])

    .addEntry('chat', [
        './src/ChatBundle/Resources/assets/js/client/room.js',
        './src/ChatBundle/Resources/assets/js/client/message.js',
        './src/ChatBundle/Resources/assets/js/client/client.js',
        './src/ChatBundle/Resources/assets/js/client/index.js',
    ])

    .addEntry('calendar', [
        './src/AppBundle/Resources/assets/js/module/calendar.js',
        // css
        './src/AppBundle/Resources/assets/scss/module/calendar.scss',
    ])

    .addEntry('profile-calendar', [
        './src/AppBundle/Resources/assets/js/profile/edit/calendar-edit.js'
    ])

    .addEntry('view-calendar', [
        './src/AppBundle/Resources/assets/js/page/calendar-view.js',
    ])

    .addEntry('contact-us-page', [
        './src/AppBundle/Resources/assets/js/module/validation.js',
        './src/AppBundle/Resources/assets/js/page/contact-us.js',

        //css
        './src/AppBundle/Resources/assets/scss/page/contact-us.scss'
    ])
;

module.exports = Encore.getWebpackConfig();