artistNextDoor.loc
==================

A Symfony project created on June 15, 2018, 4:11 pm.


Webpack configuration
-------------------------------------------------------
@see: https://symfony.com.ua/doc/current/frontend.html

#### Install Node.js ####
Download from here \
https://nodejs.org/en/download/
#### Install Yarn ####
Download package manager from here \
https://yarnpkg.com/lang/en/docs/install/#windows-stable
#### Install Webpack for Symfony ####
Run command:
`yarn add @symfony/webpack-encore --dev`
#### Include jQuery ####
Run command:
`yarn add jquery --dev`
#### SASS, LESS ####
Run command:
`yarn add --dev sass-loader node-sass`
`yarn add --dev less-loader less`
#### Load Bootstrap ####
Run command:
`yarn add bootstrap-sass --dev`
#### Load Proper ####
`yarn add proper --save`


`yarn add select2` \
`yarn add select2-bootstrap-theme` \
`yarn add bootstrap-datepicker`

`yarn run assets:dev`
`yarn run assets:production`



### Check whether the events that ended and create charge if is it ###
CRONTAB: `50 23 * * * php /var/www/stage/bin/console app:event_subscriber:charge`




