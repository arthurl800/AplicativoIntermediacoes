<?php
// index.php
ini_set('memory_limit', '2048M');  // Aumenta para 2GB
ini_set('max_execution_time', '300');  // 5 minutos
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Limpa cache antes de carregar qualquer classe
clearstatcache(true);

// Este é o único arquivo acessível diretamente, agindo como o roteador (Router).

// Carrega as classes externas (PhpSpreadsheet) e as classes internas
require 'vendor/autoload.php';

// Inclui Controllers
require_once __DIR__ . '/app/controller/UploadController.php';
require_once __DIR__ . '/app/controller/AuthController.php';
require_once __DIR__ . '/app/controller/DashboardController.php';
require_once __DIR__ . '/app/controller/AdminController.php';
require_once __DIR__ . '/app/controller/DataController.php';
require_once __DIR__ . '/app/controller/RelatorioController.php';
require_once __DIR__ . '/app/controller/NegociacaoController.php';

// Roteamento Simples: determina qual Controller e Ação (método) executar
// Se não houver parâmetros, redireciona para a landing page
if (empty($_GET['controller']) && empty($_GET['action'])) {
    header('Location: landing.php');
    exit;
}

$controllerName = $_GET['controller'] ?? 'auth';
$actionName     = $_GET['action'] ?? 'login';

// Mapeamento de Controllers
$controllers = [
    'upload'      => UploadController::class,
    'auth'        => AuthController::class,
    'dashboard'   => DashboardController::class,
    'admin'       => AdminController::class,
    'dados'       => DataController::class,
    'data'        => DataController::class,
    'relatorio'   => RelatorioController::class,
    'negociacao'  => NegociacaoController::class,
];

if (isset($controllers[$controllerName])) {
    $controllerClass = $controllers[$controllerName];
    $controller = new $controllerClass();
    
    if (method_exists($controller, $actionName)) {
        $controller->{$actionName}(); 
    } else {
        http_response_code(404);
        echo "404 - Ação '{$actionName}' não encontrada no controller '{$controllerName}'.";
    }
} else {
    http_response_code(404);
    echo "404 - Controller '{$controllerName}' não encontrado.";
}
