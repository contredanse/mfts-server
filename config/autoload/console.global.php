<?php

return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            App\Command\GenerateVideoCoversCommand::class => App\Command\GenerateVideoCoversCommandFactory::class,
            App\Command\TranscodeVideosCommand::class => App\Command\TranscodeVideosCommandFactory::class,
			App\Command\DbDumpCommand::class => App\Command\DbDumpCommandFactory::class,
        ],
    ],
    'console' => [
        'commands' => [
            App\Command\GenerateVideoCoversCommand::class,
            App\Command\TranscodeVideosCommand::class,
			App\Command\DbDumpCommand::class
        ],
    ],
];
