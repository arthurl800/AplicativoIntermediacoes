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

        // Agregados negociáveis para o operador/comprador
        $aggregates = $this->intermediacaoModel->getNegotiableAggregates(200);

    // 2. CARREGA AS VIEWS
    $base_dir = dirname(dirname(__DIR__));
    // Disponibiliza lista de colunas existentes na tabela para a view
    $availableColumns = $this->intermediacaoModel->getAvailableColumns();
    include $base_dir . '/includes/header.php';
    // A view principal para visualização dos dados (o Canvas que estamos focando)
    include $base_dir . '/app/view/dados/visualizacao_dados.php'; 
    include $base_dir . '/includes/footer.php';
    }
    
    // (Dashboard removido conforme solicitado)

    /**
     * Exibe o formulário de negociação preenchido com os valores do agregado selecionado.
     */
    public function negotiate_form() {
        $base_dir = dirname(dirname(__DIR__));
        // Verifica parâmetros (vêm da linha de negociação)
        $data = [
            'conta' => $_GET['conta'] ?? '',
            'cliente' => $_GET['cliente'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'indexador' => $_GET['indexador'] ?? '',
            'emissor' => $_GET['emissor'] ?? '',
            'vencimento' => $_GET['vencimento'] ?? '',
            'quantidade' => $_GET['quantidade'] ?? '',
            'valor_bruto' => $_GET['valor_bruto'] ?? ''
        ];

        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/dados/negotiate_form.php';
        include $base_dir . '/includes/footer.php';
    }

    /**
     * Processa os dados da negociação enviados pelo operador (POST) e exibe o resultado.
     */
    public function process_negotiation() {
        // Recebe dados do POST
        $conta = $_POST['conta'] ?? '';
        $cliente = $_POST['cliente'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $quantidade = (float)($_POST['quantidade'] ?? 0);
        $quantidade_negociada = (int)($_POST['quantidade_negociada'] ?? $quantidade);
        $valor_bruto_saida = (float)($_POST['valor_bruto_saida'] ?? 0);
        $taxa_saida = (float)($_POST['taxa_saida'] ?? 0);
        $valor_bruto_entrada = (float)($_POST['valor_bruto_entrada'] ?? 0);
        $taxa_entrada = (float)($_POST['taxa_entrada'] ?? 0);

        // Operador atual
        $auth = new AuthManager();
        $operator = $auth->getCurrentUser();

        // Cálculos
        // corretagem = valor_bruto_entrada - valor_bruto_saida
        $corretagem = $valor_bruto_entrada - $valor_bruto_saida;
        // ROA (%) = (corretagem / valor_bruto_entrada) * 100
        $roa = ($valor_bruto_entrada != 0) ? ($corretagem / $valor_bruto_entrada) * 100 : 0;
        // Retorno ao vendedor = valor_bruto_saida - corretagem? (interpretação: quanto restou ao vendedor após corretagem)
        $retorno_vendedor_valor = $valor_bruto_saida - $corretagem;
        // Porcentagem do retorno em relação ao valor bruto original importado (se fornecido)
        $valor_bruto_importado = (float)($_POST['valor_bruto_importado'] ?? $valor_bruto_saida);
        $retorno_vendedor_pct = ($valor_bruto_importado != 0) ? ($retorno_vendedor_valor / $valor_bruto_importado) * 100 : 0;

        $resultData = [
            'conta' => $conta,
            'cliente' => $cliente,
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'valor_bruto_saida' => $valor_bruto_saida,
            'taxa_saida' => $taxa_saida,
            'valor_bruto_entrada' => $valor_bruto_entrada,
            'taxa_entrada' => $taxa_entrada,
            'corretagem' => $corretagem,
            'roa' => $roa,
            'retorno_vendedor_valor' => $retorno_vendedor_valor,
            'retorno_vendedor_pct' => $retorno_vendedor_pct,
            'operator' => $operator
        ];

        // Persiste a negociação
        require_once dirname(dirname(__DIR__)) . '/app/model/NegociacaoModel.php';
        $negModel = new NegociacaoModel();
        $savedId = $negModel->save([
            'conta' => $conta,
            'cliente' => $cliente,
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'quantidade_negociada' => $quantidade_negociada,
            'valor_bruto_saida' => $valor_bruto_saida,
            'taxa_saida' => $taxa_saida,
            'valor_bruto_entrada' => $valor_bruto_entrada,
            'taxa_entrada' => $taxa_entrada,
            'corretagem' => $corretagem,
            'roa' => $roa,
            'retorno_vendedor_valor' => $retorno_vendedor_valor,
            'retorno_vendedor_pct' => $retorno_vendedor_pct,
            'operador' => $operator['username'] ?? null
        ]);

        $resultData['saved_id'] = $savedId;

        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/dados/negotiate_result.php';
        include $base_dir . '/includes/footer.php';
    }
}
