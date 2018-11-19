<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Util\MenuConvert;

//use Monolog\Logger;

class ConvertMenuCommand extends Command
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
            ->setName('convert:menu')
            ->setDescription('Convert paxton.xml menu')
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


        //$jsonOutput = dirname($input) . '/' . basename($input, '.xml') . '.json';
        //$convert = new MenuConvert($input, $jsonOutput, $this->assetsDir);

        $this->menuConvert->convert($inputFile);


        //$output->writeln('cool');
    }
}
