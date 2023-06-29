<?php

declare(strict_types=1);

namespace Neoliens\controllers;

use Neoliens\Core\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(private readonly Twig $twig, private readonly ResponseFormatter $responseFormatter)
    {
        
    }

    public function loginView(Response $response): Response
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function registerView(Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.twig');
    }
}