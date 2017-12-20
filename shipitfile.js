module.exports = function (shipit) {
    require('shipit-deploy')(shipit)
    var homedir = require('os').homedir()

    shipit.initConfig({
        production: {
            servers: 'root@45.55.63.24',
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
            servers: 'root@45.55.63.24',
            workspace: '/tmp/loldashboard',
            deployTo: '/var/www/html/loldashboard',
            repositoryUrl: 'https://github.com/dakotawashok/loldashboard.git',
            branch: 'develop',
            ignores: ['.git', 'node_modules', 'vendor'],
            rsync: ['--del'],
            keepReleases: 2,
            shallowClone: true
        },
        // production: {
        //     servers: '',
        //     workspace: '/tmp/encompass.app',
        //     deployTo: '/var/www/app.encompass.nnja.co/public_html',
        //     repositoryUrl: 'https://cubicle-ninjas.git.beanstalkapp.com/enco-encompass-app.git',
        //     dirToCopy: 'staging-build',
        //     ignores: ['.git', 'node_modules'],
        //     rsync: ['--del'],
        //     keepReleases: 2,
        //     key: homedir + '/enco.pem',
        //     shallowClone: true
        // }
    })

    // shipit.blTask('symlink-files', function () {
    //     return shipit.remote('cd /var/www/dnrs-portal/current && ln -s /var/www/files wp-content');
    // })
    //
    // shipit.on('deployed', function () {
    //     shipit.start('symlink-files')
    // })
    shipit.blTask('install', function () {
        shipit.log('Installing composer packages')
        return shipit.remote("cd " + shipit.currentPath +  "/admin/api/ && composer install &> /dev/null");
    })

    shipit.blTask('setup', function () {
        shipit.log('Symlink to blog')
        shipit.remote("cd " + shipit.currentPath +  " && ln -sf /var/www/pkdw.blog/current blog");
        shipit.log('Copying env file')
        return shipit.remoteCopy('admin/api/.env-sample',  shipit.currentPath + '/admin/api/.env')
    })

    shipit.on('deployed', function () {
        if( shipit.environment == 'staging' ) {
            shipit.start('install', 'setup')
        } else {
            // shipit.start('install', 'setup', 'configure-prod')
        }
    })
}