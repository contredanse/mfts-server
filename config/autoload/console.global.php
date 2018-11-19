<?php

return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            App\Command\ConvertMenuCommand::class => App\Command\ConvertMenuCommandFactory::class,
            App\Command\ConvertVideoCommand::class => App\Command\ConvertVideoCommandFactory::class,
            App\Command\GenerateVideoCoversCommand::class => App\Command\GenerateVideoCoversCommandFactory::class,
            App\Command\ConvertSubsCommand::class => App\Command\ConvertSubsCommandFactory::class,
            App\Command\TranscodeVideosCommand::class => App\Command\TranscodeVideosCommandFactory::class
        ],
    ],
    'console' => [
        'commands' => [
            App\Command\ConvertMenuCommand::class,
            App\Command\ConvertVideoCommand::class,
            App\Command\ConvertSubsCommand::class,
            App\Command\GenerateVideoCoversCommand::class,
            App\Command\TranscodeVideosCommand::class,
        ],
    ],
];