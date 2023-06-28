<?php

declare(strict_types = 1);


use Slim\App;
use Slim\Views\Twig;
use Neoliens\Core\Config;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Neoliens\Core\Middleware\CsrfFieldsMiddleware;
use Neoliens\Core\Middleware\StartSessionsMiddleware;
use Neoliens\Core\Middleware\ValidationErrorsMiddleware;
use Neoliens\Core\Middleware\ValidationExceptionMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);

    $app->add(MethodOverrideMiddleware::class);
    $app->add(CsrfFieldsMiddleware::class);
    $app->add('csrf');
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(ValidationErrorsMiddleware::class);
    $app->add(StartSessionsMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};
