<?php declare(strict_types=1);

namespace App\Command;

use Soluble\MediaTools\Video\Filter\NlmeansVideoFilter;
use Soluble\MediaTools\Video\Filter\ScaleFilter;
use Soluble\MediaTools\Video\SeekTime;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;
use Soluble\MediaTools\Video\VideoThumbParams;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateVideoCoversCommand extends Command
{

    /**
     * @var VideoThumbGeneratorInterface
     */
    protected $thumbGenerator;

    /**
     * @var VideoInfoReaderInterface
     */
    protected $infoReader;

    /**
     * @var array<string>
     */
    protected $supportedVideoExtensions = [
        'webm', 'mov', 'mkv'
    ];


    public function __construct(VideoThumbGeneratorInterface $thumbGenerator, VideoInfoReaderInterface $infoReader)
    {
        $this->thumbGenerator = $thumbGenerator;
        $this->infoReader = $infoReader;
        parent::__construct();
    }


    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('generate:video:covers')
            ->setDescription('Generate video covers from input directory')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('dir', 'd', InputOption::VALUE_REQUIRED),
                ])
            );
    }

    /**
     * Executes the current command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasOption('dir')) {
            throw new \Exception('Missing dir argument, use <command> <dir>');
        }
        $videoPath = $input->getOption('dir');
        if (!$videoPath || !is_dir($videoPath)) {
            throw new \Exception(sprintf("Video dir %s does not exists", $videoPath));
        }

        $output->writeln("Processing covers...");
        $output->writeln('');


        $extraCoversPath = $videoPath . '/covers';
        if (!is_dir($extraCoversPath)) {
            $ret = mkdir($extraCoversPath);
            if ($ret === false) {
                throw new \Exception(sprintf(
                    'Cannot create directory %s',
                    $extraCoversPath
                ));
            }
        }

        $videos = $this->getVideoFiles($videoPath);

        $format = 'jpg';

        foreach ($videos as $video) {
            // Make single cover
            $this->makeCover(
                $video,
                sprintf(
                    '%s/%s.%s',
                    $extraCoversPath, //dirname($video),
                    basename($video, '.' . pathinfo($video, PATHINFO_EXTENSION)),
                    $format
                ),
                new SeekTime(1.0)
            );

            $this->makeCovers($video, $extraCoversPath, $format, 5);

            $output->writeln($video);
        }


        $output->writeln("\nFinished");
    }

    public function makeCover(string $videoFile, string $outputFile, ?SeekTime $seekTime = null):void
    {

        if ($seekTime !== null) {
            $videoInfo = $this->infoReader->getInfo($videoFile);

            if ($seekTime->getTime() > $videoInfo->getDuration()) {
                $seekTime = new SeekTime($videoInfo->getDuration());
            }
        } else {
            $seekTime = new SeekTime(0.0);
        }
        $thumbParams = (new VideoThumbParams())->withSeekTime($seekTime)->withVideoFilter(
            new NlmeansVideoFilter()
        );

        $this->thumbGenerator->makeThumbnail($videoFile, $outputFile, $thumbParams);
    }

    public function makeCovers(string $videoFile, string $outputPath, string $format, int $numberOfThumbs): void
    {

        $videoInfo = $this->infoReader->getInfo($videoFile);
        $duration = $videoInfo->getDuration();

        $videoBaseName = basename($videoFile, '.' . pathinfo($videoFile, PATHINFO_EXTENSION));

        for ($i = 0; $i < $numberOfThumbs; $i++) {
            $outputFile = sprintf(
                '%s/%s-%02d.%s',
                $outputPath,
                $videoBaseName,
                $i + 1,
                $format
            );

            $time = ceil($duration / $numberOfThumbs * $i);

            $thumbParams = (new VideoThumbParams())
                ->withVideoFilter(
                    new NlmeansVideoFilter()
                )
                ->withSeekTime(new SeekTime($time));

            $this->thumbGenerator->makeThumbnail($videoFile, $outputFile, $thumbParams);
        }
    }



    /**
     * @param string $videoPath
     * @return string[]
     */
    public function getVideoFiles(string $videoPath): array
    {

        $files = (new Finder())->files()
            ->in($videoPath)
            ->name(sprintf(
                '/\.(%s)$/',
                implode('|', $this->supportedVideoExtensions)
            ));

        $videos = [];

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $videos[] = $file->getPathname();
        }

        return $videos;
    }
}
