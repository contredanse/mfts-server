<?php

declare(strict_types=1);

namespace App\Command;

use App\Infra\Db\ContredanseDb;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
                    new InputOption('file', 'f', InputOption::VALUE_REQUIRED),
                ])
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasOption('file')) {
            throw new \Exception('Missing dir argument, use <command> <file>');
        }
        $dumpFile = $input->hasOption('file') ? $input->getOption('file') : '';

        if (!is_string($dumpFile) || !is_dir(dirname($dumpFile))) {
            throw new \Exception(sprintf(
                'Dump directory %s does not exists',
                is_string($dumpFile) ? dirname($dumpFile) : ''
            ));
        }

        $output->writeln('Starting dump, it\'s gonna take a while');

        $params = $this->db->getConnectionInfo();

        $command = [
            file_exists('./bin/mysqldump') ? './bin/mysqldump' : 'mysqldump',
            '--extended-insert',
            '--no-create-db',
            '--compress',
            sprintf('--user=%s', $params['username']),
            sprintf('--password=%s', $params['password']),
            sprintf('--host=%s', $params['host']),
            $params['dbname'],
            sprintf('> %s', $dumpFile)
        ];

        $process = Process::fromShellCommandline(implode(' ', $command));
        $process->setTimeout(7200);
        $process->setIdleTimeout(30);

        try {
            $process->mustRun(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                } else {
                    echo '.';
                }
            });
        } catch (\Throwable $e) {
            $msg = str_replace($params['password'], '******', $e->getMessage());
            throw new \RuntimeException($msg);
        }

        $output->writeln("\nDump done !");

        return 1;
    }
}
