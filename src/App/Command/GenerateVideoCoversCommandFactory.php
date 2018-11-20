<?php

declare(strict_types=1);

namespace App\Command;

use Psr\Container\ContainerInterface;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;

class GenerateVideoCoversCommandFactory
{
    public function __invoke(ContainerInterface $container): GenerateVideoCoversCommand
    {
        return new GenerateVideoCoversCommand(
            $container->get(VideoThumbGeneratorInterface::class),
            $container->get(VideoInfoReaderInterface::class)
        );
    }
}
