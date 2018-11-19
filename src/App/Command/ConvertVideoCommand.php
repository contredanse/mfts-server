<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Util\MenuConvert;

class ConvertVideoCommand extends Command
{
    protected $menuConvert;

    public function __construct(MenuConvert $menuConvert)
    {
        $this->menuConvert = $menuConvert;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setName('convert:videos')
            ->setDescription('Convert paxton videos')
            ->addArgument(
                'input',
                InputArgument::OPTIONAL,
                'Paxton.xml input file'
            );
    }

    /**
     * Executes the current command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$input->hasArgument('input')) {
            throw new \Exception("Missing input file option, use <command> <file>");
        }
        $inputFile = $input->getArgument('input');
        if (!file_exists($inputFile)) {
            throw new \Exception(sprintf("Paxton xml file %s does not exists", $inputFile));
        }
        $output->writeln("Converting videos...");
        $results = $this->menuConvert->convertVideos($inputFile, ['mp4', 'webm'], false, $output);
        $output->writeln('');
        foreach ($results['failed'] ?? [] as $videoFile) {
            $output->writeln("<error>Skipping $videoFile, duration is null</error>");
        }
        $output->writeln("\nFinished");
    }
}
