<?php

// Inclut l'autoloader généré par Composer
require_once __DIR__ . "/../vendor/autoload.php";

if (
  php_sapi_name() !== 'cli' &&
  preg_match('/\.(?:png|jpg|jpeg|gif|ico)$/', $_SERVER['REQUEST_URI'])
) {
  return false;
}

use App\Config\Connection;
use App\Config\TwigEnvironment;
use App\DependencyInjection\Container;
use App\Routing\RouteNotFoundException;
use App\Routing\Router;
use App\Utils\FormError;
use Doctrine\ORM\EntityManager;
use Service\ConvertCsvToExcelService;
use Service\DownloadFileService;
use Service\MailService;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;

// Env vars - Possibilité d'utiliser le pattern Adapter
// Pour pouvoir varier les dépendances qu'on utilise
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

// BDD
$connection = new Connection();
$entityManager = $connection->init();

// Twig - Vue
$twigEnvironment = new TwigEnvironment();
$twig = $twigEnvironment->init();

// Mail Service
$mail = new MailService($entityManager);
// ConvertFile Service
$convertFile = new ConvertCsvToExcelService();
// DownloadFile Service
$downloadFile = new DownloadFileService();
// Errors
$formError = new FormError();

// Service Container
$container = new Container();
$container->set(EntityManager::class, $entityManager);
$container->set(Environment::class, $twig);
$container->set(MailService::class, $mail);
$container->set(FormError::class, $formError);
$container->set(ConvertCsvToExcelService::class, $convertFile);
$container->set(DownloadFileService::class, $downloadFile);

// Routage
$router = new Router($container);
$router->registerRoutes();

if (php_sapi_name() === 'cli') {
  return;
}

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
  $router->execute($requestUri, $requestMethod);
} catch (RouteNotFoundException $e) {
  http_response_code(404);
  echo $twig->render('utils/404.html.twig', ['title' => $e->getMessage()]);
}
