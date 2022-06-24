<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Naitzel\SilexCrud\Helper\CamelCaseHelper;

/**
 * Class GeneratorCommand.
 */
class GeneratorCommand extends AbstractCommand
{
    /**
     * configure.
     */
    protected function configure()
    {
        $this
            ->setName('generator')
            ->setDescription('Gerar arquivos a partir de um banco de dados')
            ->addOption('tables', null, InputOption::VALUE_REQUIRED, 'Define tabelas a ser gerada')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Forçar sobre escrever arquivos existente')
        ;
    }

    /**
     * Não exibir essas tabelas no crud generator.
     *
     * @return array
     */
    protected function notTables()
    {
        return array(
            'banner',
            'banner_type',
            'institutional',
            'institutional_type',
            'roles',
            'roles_access',
            'seo',
            'users',
            'users_roles',
        );
    }

    protected function ignoreCollumns()
    {
        return array(
            'created_at',
            'updated_at',
            'deleted_at',
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $call = function ($value) {
            return array_values($value)[0];
        };

        // Busca todas tabelas do banco
        $getTables = array_map($call, $this->get('db')->fetchAll('SHOW TABLES', array()));

        // Remove a tabela de usuário da lista
        $getTables = array_values(array_filter($getTables, function ($table) {
            return !in_array($table, $this->notTables());
        }));

        if (count($getTables) === 0) {
            return $output->writeln('<error>Nenhuma tabela foi encontrada.</error>');
        }

        if ($input->getOption('tables') === null) {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion('Selecione as tabelas para gerar os padrões CRUD <comment>(Deixe em branco para selecionar todas, ou informe os as tabelas Ex: "1,2" ou "tabela1,tabela2")</comment>', $getTables, implode(',', array_keys($getTables)));
            $question->setMultiselect(true);
            $tables_generate = $helper->ask($input, $output, $question);
        } else {
            $tables_in = explode(',', $input->getOption('tables'));
            $tables_generate = array();
            foreach ($tables_in as $table_in) {
                if (in_array($table_in, $getTables)) {
                    $tables_generate[] = $table_in;
                }
            }
        }

        if (count($tables_generate) === 0) {
            return $output->writeln('<error>Nenhuma tabela foi selecionada.</error>');
        }

        $output->writeln('Tabelas selecionadas: <comment>'.implode('</comment>, <comment>', $tables_generate).'</comment>');

        $tables = array();
        foreach ($tables_generate as $table_name) {
            if ($output->getVerbosity() > 32) {
                $output->writeln(sprintf('Capturando informações sobre a tabela <comment>"%s"</comment>', $table_name));
            }
            $table_info = $this->getInfoTable($table_name, $input, $output);

            if (is_array($table_info)) {
                $tables[$table_name] = $table_info;
            } else {
                if ($output->getVerbosity() > 32) {
                    $output->writeln(sprintf('<info>A tabela "%s" não será gerada.</info>', $table_name));
                }
            }
        }
        $output->writeln('Aguarde estamos gerando...');

        foreach ($tables as $table_name => $data) {
            $this->createController($table_name, $data, $input, $output);
            $this->createForm($table_name, $data, $input, $output);
            $this->createViews($table_name, $data, $input, $output);
            $this->createRoutes($table_name, $data, $input, $output);
            $this->createMenu($table_name, $data, $input, $output);
            $output->writeln(sprintf('Tabela <comment>"%s"</comment> gerada!', $table_name));
        }
    }

    /**
     * Retorna informações sobre a tablela.
     *
     * @param string $table_name
     *
     * @return array
     */
    private function getInfoTable($table_name, InputInterface $input, OutputInterface $output)
    {
        $table_column = array();
        $table_form = array();

        $table_result = $this->get('db')->fetchAll(sprintf('DESC `%s`', $table_name), array());

        $primary_key = null;
        $primary_keys = 0;
        $primary_keys_auto = 0;

        $call_map = function ($column) use (&$primary_keys, &$primary_keys_auto) {
            if ($column['Key'] === 'PRI') {
                ++$primary_keys;
            }
            if ($column['Extra'] == 'auto_increment') {
                ++$primary_keys_auto;
            }
        };

        array_map($call_map, $table_result);

        if (!($primary_keys === 1 || ($primary_keys > 1 && $primary_keys_auto === 1))) {
            return;
        }

        foreach ($table_result as $column) {

            // Ignora colunas informadas
            if (in_array(strtolower($column['Field']), $this->ignoreCollumns())) {
                continue;
            }

            if ((($primary_keys > 1 && $primary_keys_auto == 1) and ($column['Extra'] == 'auto_increment')) or ($column['Key'] == 'PRI')) {
                $primary_key = $column['Field'];
            }

            $table_result_column = array(
                'name' => $column['Field'],
                'title' => ucfirst($column['Field']),
                'primary' => $column['Field'] == $primary_key ? true : false,
                'nullable' => $column['Null'] == 'NO' ? true : false,
                'auto' => $column['Extra'] == 'auto_increment' ? true : false,
                'type' => preg_replace('/\((\d+)\)$/', '', $column['Type']),
                'lenght' => (int) preg_replace('/[^\d+]/', '', $column['Type']),
            );

            if (!in_array(strtolower($column['Field']), array('id', 'created', 'updated'))) {
                switch ($table_result_column['type']) {
                        case 'text':
                        case 'tinytext':
                        case 'mediumtext':
                        case 'longtext':
                            $type_form = 'textarea';
                            $regex = '';
                            break;

                        case 'datetime':
                            $type_form = 'text';
                            $regex = '';
                            break;

                        default:
                            $type_form = 'text';
                            $regex = '';
                            break;
                    }

                $table_form[] = array_merge(
                    $table_result_column,
                    array(
                        'type' => $type_form,
                        'validation_regex' => $regex,
                    )
                );
            }

            $table_column[] = $table_result_column;
        }

        return array(
            'primary_key' => $primary_key,
            'columns' => $table_column,
            'columns_form' => $table_form,
        );
    }

    /**
     * Gerar controller.
     *
     * @param string          $table_name
     * @param array           $data
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function createController($table_name, array $data, InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() > 32) {
            $output->writeln(sprintf('Gerando controller da tabela <comment>"%s"</comment>', $table_name));
        }
        $fs = new Filesystem();
        $dir_controller = base_path('src/Controller/Security');

        $table_camel = CamelCaseHelper::encode($table_name, true);
        $file_controller = sprintf('%s/%sController.php', $dir_controller, $table_camel);

        if ($input->getOption('overwrite') === false && is_file($file_controller)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(sprintf('O arquivo controller <comment>"%sController.php"</comment> já existe deseja subistituir? <info>(y ou n)</info>: ', $table_camel));

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln(sprintf('O arquivo <comment>"%s"</comment> não foi alterado.', $file_controller));

                return;
            }
        }

        $controller = $this->get('twig')->render('generator/controller.twig', array('table' => $table_name, 'data' => $data, 'table_camel' => $table_camel));
        $fs->dumpFile($file_controller, $controller);
    }

    /**
     * Gerar controller.
     *
     * @param string          $table_name
     * @param array           $data
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function createForm($table_name, array $data, InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $dir_form = base_path('src/Form');

        $table_camel = CamelCaseHelper::encode($table_name, true);
        $file_form = sprintf('%s/%sForm.php', $dir_form, $table_camel);

        if ($input->getOption('overwrite') === false && is_file($file_form)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(sprintf('O arquivo form <comment>"%sForm.php"</comment> já existe deseja subistituir? <info>(y ou n)</info>: ', $table_camel));

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln(sprintf('O arquivo <comment>"%s"</comment> não foi alterado.', $file_form));

                return;
            }
        }

        $controller = $this->get('twig')->render('generator/forms.twig', array('table' => $table_name, 'data' => $data, 'table_camel' => $table_camel));
        $fs->dumpFile($file_form, $controller);
    }

    /**
     * Gerar views.
     *
     * @param string          $table_name
     * @param array           $data
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function createViews($table_name, array $data, InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $dir_views = view_path('security');
        $dir_view = sprintf('%s/%s', $dir_views, $table_name);

        if ($fs->exists($dir_view) === false) {
            $fs->mkdir($dir_view, 0755);
        }

        foreach (array('theme', 'list', 'create', 'edit', 'form') as $item) {
            $file = sprintf('%s/%s.twig', $dir_view, $item);

            if ($input->getOption('overwrite') === false && is_file($file)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion(sprintf('O arquivo <comment>"%s/%s.twig"</comment> já existe deseja subistituir? <info>(y ou n)</info>: ', $table_name, $item));

                if (!$helper->ask($input, $output, $question)) {
                    continue;
                }
            }

            $list_view = $this->get('twig')->render(sprintf('generator/%s.twig', $item), array('table' => $table_name, 'data' => $data));
            $fs->dumpFile($file, $list_view);
        }
    }

    /**
     * Adicionar rotas no arquivo de rotas.
     *
     * @param string          $table_name
     * @param array           $data
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function createRoutes($table_name, array $data, InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $file_routes = base_path('src/routes_security.php');
        if ($fs->exists($file_routes)) {
            $file_contents = array_map(function ($line) {
                return preg_replace('/\n/', '', $line);
            }, file($file_routes));
            $table_routes = array();
            $exists = array(
                'index' => false,
                'list' => true,
                'create' => false,
                'edit' => false,
                'delete' => false,
            );
            $table_lower = strtolower($table_name);
            $table_camel = CamelCaseHelper::encode($table_name, true);

            foreach (array_keys($exists) as $route) {
                $lines_found = array_keys(preg_grep(sprintf('/\'Security\\\%s::%s\'/i', $table_camel, $route), $file_contents));
                $exists[$route] = count($lines_found) === 1;
            }

            if ($exists['index'] === false) {
                $table_routes[] = "\$security->get('{$table_lower}', 'Security\\{$table_camel}::index')->bind('s_{$table_lower}');";
            }
            // if ($exists['list'] === false) {
            //     $table_routes[] = "\$security->get('{$table_lower}/list', 'Security\\{$table_camel}::list')->bind('s_{$table_lower}_list');";
            // }
            if ($exists['create'] === false) {
                $table_routes[] = "\$security->match('{$table_lower}/create', 'Security\\{$table_camel}::create')->method('GET|POST')->bind('s_{$table_lower}_create');";
            }
            if ($exists['edit'] === false) {
                $table_routes[] = "\$security->match('{$table_lower}/edit/{id}', 'Security\\{$table_camel}::edit')->method('GET|POST')->bind('s_{$table_lower}_edit');";
            }
            if ($exists['delete'] === false) {
                $table_routes[] = "\$security->delete('{$table_lower}/delete/{id}', 'Security\\{$table_camel}::delete')->bind('s_{$table_lower}_delete');";
            }

            $last_line = array_keys(preg_grep('/return \$security/', $file_contents))[0];
            // Rewriting
            $rewriting = array();
            $line_blank = 0;

            foreach ($file_contents as $line => $value) {
                // Add routes
                if (count($table_routes) > 0 && $last_line == $line) {
                    $rewriting[] = '// '.$table_camel;
                    foreach ($table_routes as $route_value) {
                        $rewriting[] = $route_value;
                    }
                    $rewriting[] = '';
                }
                if (strlen(trim($value)) === 0) {
                    ++$line_blank;
                } else {
                    $line_blank = 0;
                }
                if ($line_blank <= 1) {
                    $rewriting[] = $value;
                }
            }

            $fs->dumpFile($file_routes, implode("\n", $rewriting));
        }
    }

    /**
     * Adicionar link no menu.
     *
     * @param string          $table_name
     * @param array           $data
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function createMenu($table_name, array $data, InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $file_menus = view_path('menu.twig');

        if ($fs->exists($file_menus)) {
            $file_contents = array_map(function ($line) {
                return preg_replace('/\n/', '', $line);
            }, file($file_menus));
            $table_lower = strtolower($table_name);
            $table_upper = ucfirst($table_name);

            if (!preg_grep(sprintf('/\{\{([ ]*)path\(([ ]*)\'%s\'([ ]*)\)/', $table_lower), $file_contents)) {
                $file_contents[] = '<li {% if menu_selected is defined and menu_selected == \''.$table_lower.'\' %}class="active"{% endif %}>';
                $file_contents[] = "\t<a href=\"{{ path('s_{$table_lower}') }}\">";
                $file_contents[] = "\t\t<i class=\"fa fa-bars\"></i> <span>{$table_upper}</span>";
                $file_contents[] = "\t</a>";
                $file_contents[] = '</li>';
            }

            $fs->dumpFile($file_menus, implode("\n", $file_contents));
        }
    }
}
