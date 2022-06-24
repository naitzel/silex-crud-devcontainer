<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

// Routes Website
$route = $app['controllers_factory'];

// Rotas Painel
$route->mount(sprintf('/%s', $app['security_path']), require(__DIR__.'/routes_security.php'));

// Renderizar imagens
$route->get('img/{path}/{imagem}', function (Silex\Application $app, Symfony\Component\HttpFoundation\Request $request, $path, $imagem) {
    return $app['glide']->outputImage(sprintf('%s/%s', $path, $imagem), $request->query->all());
})->bind('imagem');

$route->get('/', 'Home::index')->bind('web_home');

return $route;
