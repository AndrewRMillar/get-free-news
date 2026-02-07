<?php

declare(strict_types=1);

namespace public;

require_once __DIR__ . '/../vendor/autoload.php';

use Infrastructure\GraphQL\Controller;
use Infrastructure\Security\CsrfGuard;
use Config\Paths;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;

$logger = new Logger('article_extractor');
$logger->pushHandler(new StreamHandler(Paths::DEBUG_LOG, Level::Debug));

$container = require __DIR__ . '/container.php';
$csrfGuard = $container->get(CsrfGuard::class);
$controller = $container->get(Controller::class);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

session_start();
$csrfGuard->check();

$input = json_decode(file_get_contents('php://input'), true);

$logger->info('Input from the index.html request', ['inpunt', $input]);

$response = $controller->handleRequest($input);

echo $response;
