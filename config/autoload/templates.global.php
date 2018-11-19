<?php declare(strict_types=1);


return [
    'templates' => [
        'extension' => 'html.twig',
        'paths' => [
            // namespace / path pairs
            //
            // Numeric namespaces imply the default/main namespace. Paths may be
            // strings or arrays of string paths to associate with the namespace.
        ],
    ],
    'twig' => [
        'cache_dir'      => 'data/cache/twig',
        'assets_url'     => '/',
        //'assets_version' => '',
        'extensions' => [
            // extension service names or instances
        ],
        'runtime_loaders' => [
            // runtime loaders names or instances
        ],
        'globals' => [
            // Global variables passed to twig templates
            'ga_tracking' => getenv('GA_CODE'),
            'ga_enabled' => strtoupper(getenv('GA_ENABLED') ?? '') === 'ON',
        ],
        //'timezone' => 'default timezone identifier, e.g.: America/New_York',
        //'optimizations' => -1, // -1: Enable all (default), 0: disable optimizations
        //'autoescape' => 'html', // Auto-escaping strategy [html|js|css|url|false]
    ]
];
