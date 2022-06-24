CRUD admin
===================

Enviando e-mail
-------------------

Para enviar e-mail usando o swiftmailer edite o arquivo
`src\Provider\SwiftmailerServiceProvider.php` adicionando as configurações
necessárias, host, port, username, password, from, encryption, auth_mode.

Exemplo configuração servidor gmail.

```php

    // ...
    $app['swiftmailer.options'] = array(
        'host' => 'smtp.gmail.com',
        'port' => '465',
        'username' => 'rogerio@gmail.com',
        'password' => 'senha123',
        'from' => 'rogerio@gmail.com',
        'encryption' => 'ssl',
        'auth_mode' => null,
    );
    // ...

```

Enviando e-mail

```php

    // ...
    $message = \Swift_Message::newInstance();
    $message->setSubject('assunto');
    $message->setFrom(array($this->get('swiftmailer.options')['from']));
    $message->setTo(array('destinatario@gmail.com'));
    $message->setBody('Corpo do e-mail');

    if (!$this->get('mailer')->send($message)) {
        // Erro envio
    } else {
        // Email enviado
    }

    // ...

```

[Manual swiftmailer](http://swiftmailer.org/docs/introduction.html)
