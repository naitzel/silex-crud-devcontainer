<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Console\Question\Question;
use RuntimeException;

/**
 * Class UserCreateCommand.
 */
class UserCreateCommand extends AbstractCommand
{
    /**
     * configure.
     */
    protected function configure()
    {
        $this
            ->setName('user:new')
            ->setDescription('Adicionar um novo usuário')
            ->addOption('no-password', null, InputOption::VALUE_NONE, 'Não validar força da senha.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // Capturar nome
        $question_name = new Question('<comment>Nome:</comment> ');
        $question_name->setValidator(function ($answer) {
            if ('' === trim($answer) || strlen(trim($answer)) < 5) {
                throw new RuntimeException('Preencha o nome completo, o nome deve ter no mínimo 5 caracteres.');
            }

            return $answer;
        });

        $name = $helper->ask($input, $output, $question_name);

        // Capturar nome de usuário
        $question_username = new Question('<comment>Nome de usuário:</comment> ');
        $question_username->setValidator(function ($answer) {
            if ('' === trim($answer) || strlen(trim($answer)) < 5) {
                throw new RuntimeException('Preencha o nome de usuário, o nome de usuário deve ter no mínimo 5 caracteres.');
            }

            return $answer;
        });

        $username = $helper->ask($input, $output, $question_username);

        // Capturar email
        $question_email = new Question('<comment>E-mail:</comment> ');
        $question_email->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Preencha o e-mail, informe um e-mail válido.');
            }

            return $answer;
        });

        $email = $helper->ask($input, $output, $question_email);

        // Capturar senha
        $question_password = new Question('<comment>Senha:</comment> ');
        $question_password->setValidator(function ($answer) use ($input) {
            if ('' === trim($answer) || strlen(trim($answer)) < 3) {
                throw new RuntimeException('Preencha a senha, sua senha deve ter no mínimo 3 caracteres.');
            }

            if (!$input->getOption('no-password') && $this->testPassword($answer) < 15) {
                throw new RuntimeException('Crie uma senha mais forte tente combinar numeros e caracteres especiais.');
            }

            return $answer;
        });
        $question_password->setHidden(true);
        $question_password->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question_password);

        // Criar novo usuário
        $user = new User($username, $password);
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());

        $dataAtual = new \DateTime();

        try {
            $update_query = 'INSERT INTO `users` (`username`, `password`, `email`, `name`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?)';
            $this->get('db')->executeUpdate($update_query, array($username, $password, $email, $name, $dataAtual->format('Y-m-d H:i:s'), $dataAtual->format('Y-m-d H:i:s')));

            $output->writeln('<fg=green>Usuário criado com sucesso</fg=green>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<fg=red>Não foi possível criar o usuário: "%s"</fg=red>', $e->getMessage()));
        }
    }

    private function testPassword($password)
    {
        if (strlen($password) == 0) {
            return 0;
        }

        $strength = 0;

        $length = strlen($password);

        /*
         * Verificar se a senha não é toda minúscula
         */
        if (strtolower($password) != $password) {
            ++$strength;
        }

        /*
         * Verificar se a senha não é toda maiúscula
         */
        if (strtoupper($password) == $password) {
            ++$strength;
        }

        /*
         * Verificar se a senha tem mais de 5
         */
        if ($length >= 10) {
            ++$strength;
        }

        /*
         * Verificar se a senha tem mais de 10 caracteres
         */
        if ($length >= 15) {
            ++$strength;
        }

        /*
         * Verificar se a senha tem mais de 15 caracteres
         */
        if ($length >= 20) {
            ++$strength;
        }

        /*
         * Verificar se a senha tem caracteres
         */
        preg_match_all('/[A-z]/', $password, $chars);
        $strength += count($chars[0]);

        /*
         * Verificar se a senha tem numero
         */
        preg_match_all('/[0-9]/', $password, $number);
        $strength += count($number[0]);

        if (count($chars[0]) > 0 && count($number[0])) {
            $strength += ((count($chars[0]) + count($number[0])) / 2);
        }

        /*
         * Verificar se a senha tem caracteres especiais
         */
        preg_match_all("/[|!@#$%&*\/=?,;.:\-_+~^\\\]/", $password, $specialchars);
        $strength += count($specialchars[0]);

        /*
         * Verificar quantos caracteres não repetidos existe
         */
        $strength += count(array_unique(str_split($password)));

        return (int) ceil($strength);
    }
}
