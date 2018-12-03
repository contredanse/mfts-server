<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ContredanseDb;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbDumpCommand extends Command
{
    /**
     * @var ContredanseDb
     */
    private $db;

    public function __construct(ContredanseDb $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName('db:dump')
            ->setDescription('Dump external database')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('dir', 'd', InputOption::VALUE_REQUIRED),
                ])
            );
    }

    /**
     * Executes the current command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$input->hasOption('dir')) {
            throw new \Exception('Missing dir argument, use <command> <dir>');
        }
        $dumpDir = $input->hasOption('dir') ? $input->getOption('dir') : '';
        if (!is_string($dumpDir) || !is_dir($dumpDir)) {
            throw new \Exception(sprintf(
                'Dump directory dir %s does not exists',
                is_string($dumpDir) ? $dumpDir : ''
            ));
        }

        $output->writeln('Starting dump');

        $output->writeln('');

        $output->writeln("\nFinished");
    }
}
