module.exports = function (shipit) {
    require('shipit-deploy')(shipit)
    var homedir = require('os').homedir()

    shipit.initConfig({
        production: {
            servers: 'root@67.207.92.41',
            workspace: '/tmp/loldashboard',
            deployTo: '/var/www/html/loldashboard',
            repositoryUrl: 'https://github.com/dakotawashok/loldashboard.git',
            branch: 'master',
            ignores: ['.git', 'node_modules', 'vendor'],
            rsync: ['--del'],
            keepReleases: 2,
            shallowClone: true,
            key: homedir + '/loldashboard.pem'
        },

        staging: {
            servers: 'root@67.207.92.41',
            workspace: '/tmp/loldashboard',
            deployTo: '/var/www/html/staging_loldashboard',
            repositoryUrl: 'https://github.com/dakotawashok/loldashboard.git',
            branch: 'develop',
            ignores: ['.git', 'node_modules', 'vendor'],
            rsync: ['--del'],
            keepReleases: 2,
            shallowClone: true,
            key: homedir + '/loldashboard.pem'
        },
    })

    shipit.blTask('composer_install', function () {
        shipit.log('Installing composer packages')
        return shipit.remote("cd " + shipit.currentPath + " && composer install");
    })

    shipit.blTask('setup', function () {
        shipit.log('Copying env file')
        if (shipit.environment == 'production') {
            shipit.remoteCopy('.production_env',  shipit.currentPath + '/.env')
        } else {
            shipit.remoteCopy('.staging_env',  shipit.currentPath + '/.env')
        }
        shipit.log('Making the laravel log file');
        shipit.remote("cd " + shipit.currentPath + "/storage/logs && touch laravel.log");
        shipit.log('Fixing folder permissions');
        return shipit.remote("chown -R www-data:www-data " + shipit.releasePath);
    })

    shipit.on('deployed', function () {
        shipit.start('composer_install', 'setup')
    })
}