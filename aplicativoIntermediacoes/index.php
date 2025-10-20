<?php
// index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Este é o único arquivo acessível diretamente, agindo como o roteador (Router).

// Carrega as classes externas (PhpSpreadsheet) e as classes internas
require 'vendor/autoload.php';

// Inclui Controllers
require_once __DIR__ . '/app/controller/UploadController.php';
require_once __DIR__ . '/app/controller/AuthController.php';
require_once __DIR__ . '/app/controller/DashboardController.php';
require_once __DIR__ . '/app/controller/AdminController.php';
require_once __DIR__ . '/app/controller/DataController.php';

// Roteamento Simples: determina qual Controller e Ação (método) executar
$controllerName = $_GET['controller'] ?? 'auth'; // Padrão AGORA é 'auth'
$actionName     = $_GET['action'] ?? 'login';    // Padrão AGORA é 'login'

// Mapeamento de Controllers
$controllers = [
    'upload'    => UploadController::class,
    'auth'      => AuthController::class,
    'dashboard' => DashboardController::class,
    'admin'     => AdminController::class,
    'dados'     => DataController::class,
    'data'      => DataController::class,
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
