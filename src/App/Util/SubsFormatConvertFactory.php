<?php declare(strict_types=1);

namespace App\Util;

use Psr\Container\ContainerInterface;

class SubsFormatConvertFactory
{
    public function __invoke(ContainerInterface $container): SubsFormatConvert
    {

        $config          = $container->get('config');

        $convertConfig = $config['mfts-convert'] ?? [];

        //$inputPath = $convertConfig['assets_input_path'];
        //$outputPath = $convertConfig['assets_output_path'];

        return new SubsFormatConvert(
            $container->get(MenuConvert::class)
        );
    }
}
