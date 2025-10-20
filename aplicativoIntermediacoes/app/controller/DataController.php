<?php
// app/controller/DataController.php

require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacaoModel.php';

class DataController {
    private $authManager;
    private $intermediacaoModel;

    public function __construct() {
        $this->authManager = new AuthManager();
        $this->intermediacaoModel = new IntermediacaoModel();
        
        // Proteção: Apenas usuários logados podem acessar este controller
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            exit;
        }
    }

    // Ação Padrão: Redireciona para a visualização principal
    public function index() {
        $this->visualizar();
    }

    // Ação principal para visualizar os dados (tabela, filtros, etc.)
    public function visualizar() {
        // Coleta e limpa os filtros da URL
        $filters = [
            'mercado'     => trim($_GET['mercado'] ?? ''),
            'sub_mercado' => trim($_GET['sub_mercado'] ?? ''),
            'ativo'       => trim($_GET['ativo'] ?? ''),
        ];

        // Verifica se algum filtro foi aplicado
        $isFiltered = count(array_filter($filters)) > 0;

        // 1. CHAMA O MODEL PARA OBTER OS DADOS
        // Suporta parâmetro opcional 'columns' (ex: columns=Conta,Nome,Produto)
        $requestedColumns = [];
        if (!empty($_GET['columns'])) {
            $requestedColumns = array_filter(array_map('trim', explode(',', $_GET['columns'])));
        }

        if (!empty($requestedColumns)) {
            $dados = $this->intermediacaoModel->getDataWithColumns($requestedColumns);
        } elseif ($isFiltered) {
            // Se houver filtros, carrega os dados filtrados
            $dados = $this->intermediacaoModel->getFilteredData($filters);
        } else {
            // Se não houver filtros, carrega os dados padrão (limitado a 100)
            $dados = $this->intermediacaoModel->getAllData();
        }

    // 2. CARREGA AS VIEWS
    $base_dir = dirname(dirname(__DIR__));
    // Disponibiliza lista de colunas existentes na tabela para a view
    $availableColumns = $this->intermediacaoModel->getAvailableColumns();
    include $base_dir . '/includes/header.php';
    // A view principal para visualização dos dados (o Canvas que estamos focando)
    include $base_dir . '/app/view/dados/visualizacao_dados.php'; 
    include $base_dir . '/includes/footer.php';
    }
    
    // Ação para o Dashboard (ainda não implementada)
    public function dashboard() {
        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        // View de Dashboard
        echo "<main><h2>Dashboard de Análise</h2><p>Página em construção. Implemente aqui os KPIs e gráficos de sumarização.</p></main>";
        include $base_dir . '/includes/footer.php';
    }
}
