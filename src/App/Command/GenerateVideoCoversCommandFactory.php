<?php declare(strict_types=1);

namespace App\Command;

use App\Util\MenuConvert;
use Psr\Container\ContainerInterface;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;
use Soluble\MediaTools\Video\VideoThumbGenerator;
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
