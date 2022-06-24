<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Helper\TableHelper;

/**
 * Class GeneratorCommand.
 */
class ServerCommand extends AbstractCommand
{
    /**
     * configure.
     */
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Executar Servidor built-in em PHP')
            ->setDefinition(array(
                new InputArgument('address', InputArgument::OPTIONAL, 'endereço:porta', '127.0.0.1:8000'),
                new InputOption('docroot', 'd', InputOption::VALUE_REQUIRED, 'Diretório web', realpath(__DIR__ . '/../../pubilc')),
                new InputOption('file', 'f', InputOption::VALUE_REQUIRED, 'Arquivo web', 'index.php'),
            ))
            ->addOption('info', '-i', InputOption::VALUE_NONE, 'Exibir informações');
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return version_compare(phpversion(), '5.4.0', '>');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documentRoot = realpath($input->getOption('docroot'));
        if (!is_dir($documentRoot)) {
            $output->writeln(sprintf('<error>O diretório raiz do documento "% s" não existe</error>', $documentRoot));

            return 1;
        }

        $address = $input->getArgument('address');
        if (false === strpos($address, ':')) {
            $output->writeln('O endereço tem de estar sob a forma <comment>endereço:porta</comment>.');

            return 1;
        }

        $file = sprintf('%s/%s', $documentRoot, $input->getOption('file'));
        if (!is_file($file)) {
            $output->writeln(sprintf('<error>O arquivo "%s" não existe, o arquivo deve estar dentro do diretório raiz</error>', $file));

            return 1;
        }

        $output->writeln(sprintf("Servidor em execução <info>http://%s</info>\n", $address));

        if ($input->getOption('info')) {
            $output->writeln('Informações sobre a conexão');

            /**
             * @var $table TableHelper
             */
            $table = $this->getHelperSet()->get('table');
            $table->setLayout(TableHelper::LAYOUT_DEFAULT);

            foreach ($this->get('db')->getParams() as $key => $value) {
                $table->addRow(array($key, $value));
            }

            $table->render($output);
        }

        $output->writeln('Encerre o servidor com CONTROL-C.');

        if (null === $builder = $this->createPhpProcessBuilder($output, $address, $documentRoot, $file)) {
            return 1;
        }

        $builder->setWorkingDirectory($documentRoot);
        $builder->setTimeout(null);
        $process = $builder->getProcess();
        if (OutputInterface::VERBOSITY_VERBOSE > $output->getVerbosity()) {
            $process->disableOutput();
        }

        $this
            ->getHelper('process')
            ->run($output, $process, null, null, OutputInterface::VERBOSITY_VERBOSE);
        if (!$process->isSuccessful()) {
            $output->writeln('<error>O servidor terminou inesperadamente</error>');
            if ($process->isOutputDisabled()) {
                $output->writeln('<error>Execute o comando novamente com a opção -v para obter mais detalhes</error>');
            }
        }

        return $process->getExitCode();
    }

    private function createPhpProcessBuilder(OutputInterface $output, $address, $documentRoot, $file)
    {
        $router = realpath($documentRoot);
        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find()) {
            $output->writeln('<error>Incapaz de encontrar o binário PHP para executar o servidor</error>');

            return;
        }

        return new ProcessBuilder(array($binary, '-S', $address, '-t', $router, $file));
    }
}
