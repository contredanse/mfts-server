<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Util\MenuConvert;

class ConvertSubsCommand extends Command
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
            ->setName('convert:subs')
            ->setDescription('Convert paxton videos subs')
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

        $output->writeln("Converting subs...");
        $results = $this->menuConvert->convertSubs($inputFile, $output);
        $output->writeln('');
        foreach ($results['failed'] ?? [] as $file) {
            $output->writeln("<error>Cannot convert $file.</error>");
        }
        $output->writeln("\nFinished");
    }
}
