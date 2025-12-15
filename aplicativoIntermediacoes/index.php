<?php
// index.php
ini_set('memory_limit', '512M');

// Configuração de erros para o ambiente de PRODUÇÃO
error_reporting(0); // Não exibir erros
ini_set('display_errors', 0); // Desabilitar a exibição de erros
ini_set('log_errors', 1); // Habilitar o registro de erros em um arquivo de log

// Este é o único arquivo acessível diretamente, agindo como o roteador (Router).

// Carrega as classes externas (PhpSpreadsheet) e as classes internas
require 'vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';

// Inclui Controllers
require_once __DIR__ . '/app/controller/UploadController.php';
require_once __DIR__ . '/app/controller/AuthController.php';
require_once __DIR__ . '/app/controller/DashboardController.php';
require_once __DIR__ . '/app/controller/AdminController.php';
require_once __DIR__ . '/app/controller/DataController.php';
require_once __DIR__ . '/app/controller/RelatorioController.php';
require_once __DIR__ . '/app/controller/NegociacaoController.php';

// Roteamento Simples: determina qual Controller e Ação (método) executar
$controllerName = $_GET['controller'] ?? 'auth'; // Padrão AGORA é 'auth'
$actionName     = $_GET['action'] ?? 'login';    // Padrão AGORA é 'login'

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
