<?php declare(strict_types=1);

namespace App\Util;

use Psr\Container\ContainerInterface;
use Soluble\MediaTools\Video\VideoAnalyzerInterface;
use Soluble\MediaTools\Video\VideoConverterInterface;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;

class MenuConvertFactory
{
    public function __invoke(ContainerInterface $container): MenuConvert
    {

        $config          = $container->get('config');

        $convertConfig = $config['mfts-convert'] ?? [];

        $assetsDir = $convertConfig['assets_input_path'];
        $outputDir = $convertConfig['assets_output_path'];
        $ffbinariesPath = $convertConfig['ffbinaries_path'];



        return new MenuConvert(
            $assetsDir,
            $outputDir,
            $container->get(VideoInfoReaderInterface::class),
            $container->get(VideoThumbGeneratorInterface::class),
            $container->get(VideoConverterInterface::class),
            $container->get(VideoAnalyzerInterface::class),
            $ffbinariesPath
        );
    }
}
