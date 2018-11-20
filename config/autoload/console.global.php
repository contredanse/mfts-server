<?php

return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            App\Command\GenerateVideoCoversCommand::class => App\Command\GenerateVideoCoversCommandFactory::class,
            App\Command\TranscodeVideosCommand::class => App\Command\TranscodeVideosCommandFactory::class
        ],
    ],
    'console' => [
        'commands' => [
            App\Command\GenerateVideoCoversCommand::class,
            App\Command\TranscodeVideosCommand::class,
        ],
    ],
];