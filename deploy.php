<?php

require_once 'recipe/common.php';

require_once __DIR__ . '/config/init-env.php';

use function Deployer\{host, task, run, set, get, add, before, after};

// In order to create keys between client and server
//
// 1. Go to gitlab project and add a new key
//    - Give the name of your computer,
//      and fill key field with the content of
//      `cat ~/.ssh/id_rsa.pub`
//    You should be able now to connect to gitlab
//    - Test with
//      `ssh -T git@gitlab.com`
// 2. Share your public key on the destination server
//    - `ssh-copy-id <user>@<hostname_or_ip>`
//    Test connection with the server
//    - ssh <user>@<hostname_or_ip>
//
// Then you should be able to deploy on the server by typing
// `dep deploy production -p` (-p = parrallell)

set('repository', 'git@gitlab.com:nuvolia/mfts_backend.git');
set('keep_releases', 5);

// Only available from deployer 4.1 (see https://deployer.org/blog/deployer-410-release)
// remove if not needed
//set('ssh_type', 'native');
//set('ssh_multiplexing', true);
//set('ssh_type', 'native');
//set('ssh_multiplexing', true);

set('http_user', 'www-data');
set('shared_files', [
    'config/autoload/local.php',
]);

set('shared_dirs', [
    'config/jwt',
]);

set('writable_dirs', [
    'data',
    'data/log',
    'data/cache',
    'data/cache/twig'
]);
set('writable_use_sudo', false);

$php_bin = '/usr/bin/php7.2';

host('emd-webserver')
    ->user('intelart')
    //->forwardAgent() // You can use identity key, ssh config, or username/password to auth on the server.
    ->stage('production')
    // Specific to EMD where the php7.1 binary is somewhere else
    ->set('bin/php', $php_bin)
    ->set('bin/composer', '/usr/bin/php7.1 /usr/local/bin/composer')
    ->set('deploy_path', '/web/www/apps.emdmusic.com'); // Define the base path to deploy your project to.



task('deploy:create_cache_dir', function () {
    // set cache dir
    set('cache_dir', '{{release_path}}/data/cache');

    // Remove cache dir if it exist
    run('if [ -d "{{cache_dir}}" ]; then rm -rf {{cache_dir}}; fi');

    // Create TWIG cache dir
    run('mkdir -p {{cache_dir}}/twig');

    // Set rights
    run("chmod -R g+w {{cache_dir}}");

})->desc('Create cache dir');


task('deploy:set_media_preview_dir_permissions', function () {
    set('media_preview_dir', '{{deploy_path}}/shared/public/media/preview');
    run("chmod o+w {{media_preview_dir}}");
})->desc('Set media preview dir permissions');

task('deploy:set_report_dir_permissions', function () {
    set('report_dir', '{{release_path}}/reports');
    run("chgrp {{http_user}} -R {{report_dir}}");
    run("chmod -R g+w {{report_dir}}");
})->desc('Set report_dir permission');


task('deploy:clear_config_cache', function () use ($php_bin) {

    // Remove cache dir if it exist
    run("$php_bin {{release_path}}/bin/clear-config-cache.php");

})->desc('Clear expressive config cache');



/*
task('deploy:vendors-php7', function () {
    // waiting support in deployer v4...
})*/

task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:create_cache_dir',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_config_cache',
    'deploy:set_media_preview_dir_permissions',
    'deploy:set_report_dir_permissions',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');




// In order to reload php-fpm, add the following line into /etc/sudoers
//
// > intelart ALL = NOPASSWD: /etc/init.d/nuvolia-phpfpm
//
task('reload:php-fpm', function () {
    run('sudo /etc/init.d/php7.1-fpm reload');
    //run('sudo /etc/init.d/nuvolia-phpfpm reload');
});


after('deploy', 'reload:php-fpm');
after('rollback', 'reload:php-fpm');
