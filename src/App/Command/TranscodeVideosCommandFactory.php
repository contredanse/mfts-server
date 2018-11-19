<?php declare(strict_types=1);

namespace App\Command;

use Psr\Container\ContainerInterface;
use Soluble\MediaTools\Video\VideoAnalyzerInterface;
use Soluble\MediaTools\Video\VideoConverterInterface;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;

class TranscodeVideosCommandFactory
{
    public function __invoke(ContainerInterface $container): TranscodeVideosCommand
    {
        return new TranscodeVideosCommand(
            $container->get(VideoInfoReaderInterface::class),
            $container->get(VideoAnalyzerInterface::class),
            $container->get(VideoConverterInterface::class)
        );
    }
}
