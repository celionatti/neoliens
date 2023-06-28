<?php

declare(strict_types=1);

use Neoliens\controllers\HomeController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/', [HomeController::class, 'index']);
    });
};