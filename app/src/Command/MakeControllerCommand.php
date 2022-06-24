<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Naitzel\SilexCrud\Helper\CamelCaseHelper;

/**
 * Class MakeControllerCommand.
 */
class MakeControllerCommand extends AbstractCommand
{

    /**
     * configure.
     */
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->setDescription('Gerar controller base')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '[Path]/[ControllerName]'
            )
        ;
    }

    /**
     * Recuperar diretorio antes do controller.
     */
    private function beforeLast($string, $inthat)
    {
        return substr($string, 0, strripos($string, $inthat));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = str_replace(["Controller", "Controller.php", ".php"], "", $input->getArgument('name'));

        $path = base_path('src/Controller/' . $name . 'Controller.php');
        $controller = (substr(strrchr($name, "/"), 1) !== false ? substr(strrchr($name, "/"), 1) : $name);

        $fs = new Filesystem();

        if($fs->exists($path)){
            return $output->writeln('<error>O controller '.$controller.' já existe.</error>');
        }else{
            $name_space = (str_replace("/", "\\", $this->beforeLast($name, '/')) !== "" ? "\\".str_replace("/", "\\", $this->beforeLast($name, '/')) : "");
            $table_camel = CamelCaseHelper::encode($controller, true);

            $file = $this->get('twig')->render('generator/make:controller.twig', array('name_space' => $name_space, 'table_camel' => $table_camel));
            $fs->dumpFile($path, $file);
        }

        return $output->writeln(sprintf('<fg=green>Controller <options=bold>"%s"</> criado com sucesso!</>', $controller));
    }
}
