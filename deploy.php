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

set('repository', 'git@github.com:contredanse/mfts-server.git');
set('keep_releases', 5);

set('http_user', 'www-data');
set('shared_files', [
    'config/autoload/local.php',
    '.env'
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

host(getenv('DEPLOY_HOST'))
    ->stage('production')
    ->user(getenv('DEPLOY_USER'))
    ->identityFile(getenv('DEPLOY_IDENTITY_FILE'))
    ->set('deploy_path', getenv('DEPLOY_PATH'));

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

task('deploy:clear_config_cache', function () {
    // Remove cache dir if it exist
    run("php {{release_path}}/bin/clear-config-cache.php");
})->desc('Clear expressive config cache');


task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:create_cache_dir',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_config_cache',
    'deploy:symlink',
    'cleanup',
])->desc('Deploy your project');

// In order to reload php-fpm, add the following line into /etc/sudoers
//
// > deployer ALL = NOPASSWD: /bin/systemctl reload php7.2-fpm
//
task('reload:php-fpm', function () {
    run(getenv('DEPLOY_CMD_OPCACHE_RESET'));
    //run('sudo /etc/init.d/nuvolia-phpfpm reload');
});

after('deploy', 'reload:php-fpm');
after('rollback', 'reload:php-fpm');
