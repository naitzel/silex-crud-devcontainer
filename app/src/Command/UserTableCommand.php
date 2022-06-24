<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\DriverException;

/**
 * Class UserTableCommand.
 */
class UserTableCommand extends AbstractCommand
{
    /**
     * configure.
     */
    protected function configure()
    {
        $this
            ->setName('user:check')
            ->setDescription('Cria ou altera a tabela usuário no banco')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'Limpar a tabela usuário, <comment>atenção esta opção remove todos usuários</comment>.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Tabela já existe, estamos verificando se existe todos os campos aguarde...</info>');

        // Criar se não existir tabela usuario
        $this->get('db')->executeQuery("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(40) NOT NULL COLLATE 'utf8_unicode_ci',
                `password` VARCHAR(160) NOT NULL COLLATE 'utf8_unicode_ci',
                `email` VARCHAR(120) NOT NULL COLLATE 'utf8_unicode_ci',
                `name` VARCHAR(80) NOT NULL COLLATE 'utf8_unicode_ci',
                `created` DATETIME NOT NULL,
                `updated` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_USERNAME` (`username`),
                UNIQUE INDEX `UNIQ_EMAIL` (`email`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=1
        ");

        $fields = array(
            'id' => ' ALTER TABLE `users` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT ADD PRIMARY KEY (`id`); ',
            'username' => " ALTER TABLE `users` ADD COLUMN `username` VARCHAR(40) NOT NULL COLLATE 'utf8_unicode_ci', ADD UNIQUE INDEX `u_username` (`username`); ",
            'password' => " ALTER TABLE `users` ADD COLUMN `password` VARCHAR(160) NOT NULL COLLATE 'utf8_unicode_ci'; ",
            'email' => " ALTER TABLE `users` ADD COLUMN `email` VARCHAR(120) NOT NULL COLLATE 'utf8_unicode_ci', ADD UNIQUE INDEX `u_email` (`email`); ",
            'name' => " ALTER TABLE `users` ADD COLUMN `name` VARCHAR(80) NOT NULL COLLATE 'utf8_unicode_ci'; ",
            'created' => ' ALTER TABLE `users` ADD COLUMN `created` DATETIME NOT NULL; ',
            'updated' => ' ALTER TABLE `users` ADD COLUMN `updated` DATETIME NOT NULL; ',
        );

        if ($input->getOption('truncate')) {
            $output->writeln('<info>Limpando tabela usuário...</info>');
            $output->writeln('');
            $this->get('db')->executeQuery('TRUNCATE `users`;');
        }

        try {
            $columns = $this->get('db')->fetchAll('DESCRIBE users;');

            $output->writeln('');
            $output->writeln('<comment>Verificando colunas</comment>');
            foreach ($fields as $field => $sql) {
                $has = array_filter($columns, function ($column) use ($field) {
                    return $column['Field'] === $field;
                });

                if (count($has) === 0) {
                    try {
                        $this->get('db')->executeQuery($sql);
                        $output->writeln(sprintf("<comment>%s</comment>\t<fg=cyan>[ADD]</fg=cyan>", $field));
                    } catch (DriverException $e) {
                        $output->writeln(sprintf("<comment>%s</comment>\t<error>[ERROR]</error>", $field));
                    }
                } else {
                    $output->writeln(sprintf("<comment>%s</comment>\t<info>[OK]</info>", $field));
                }
            }
        } catch (TableNotFoundException $e) {
            $output->writeln('<error>Tabela não existe.</error>');
        }
    }
}
