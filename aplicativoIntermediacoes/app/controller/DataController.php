<?php
// app/controller/DataController.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacaoModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/AuditLogger.php';

class DataController {
    private $authManager;
    private $intermediacaoModel;
    private $auditLogger;

    public function __construct() {
        $this->authManager = new AuthManager();
        $this->intermediacaoModel = new IntermediacaoModel();
        $this->auditLogger = AuditLogger::getInstance();
        
        // Proteção: Apenas usuários logados podem acessar este controller
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            exit;
        }
    }

    // Redireciona para a visualização principal
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

        // Chama o Model para obtenção dos dados
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
        
        // Registra visualização de dados
        $filtroAplicado = $isFiltered ? json_encode($filters) : 'sem filtros';
        $this->auditLogger->logView('DADOS', "Visualização de intermediações - Filtros: {$filtroAplicado}");
        
        // Agregados negociáveis para o operador/comprador
        $aggregates = $this->intermediacaoModel->getNegotiableAggregates(200);

    // Carrega as Views
    $base_dir = dirname(dirname(__DIR__));
    // Disponibiliza lista de colunas existentes na tabela para a view
    $availableColumns = $this->intermediacaoModel->getAvailableColumns();
    include $base_dir . '/includes/header.php';
    // A view principal para visualização dos dados
    include $base_dir . '/app/view/dados/ViewData.php'; 
    include $base_dir . '/includes/footer.php';
    }
    
     /**
     * Exibe o formulário de negociação preenchido com os valores do agregado selecionado.
     */
    public function negotiate_form() {
        $base_dir = dirname(dirname(__DIR__));
        // Verifica parâmetros (vêm da linha de negociação)
        // Mapeia possíveis nomes dos parâmetros
        $data = [
            'conta' => $_GET['conta'] ?? ($_GET['Conta'] ?? ''),
            'nome' => $_GET['nome'] ?? ($_GET['cliente'] ?? ($_GET['Nome'] ?? '')),
            'ativo' => $_GET['ativo'] ?? ($_GET['Ativo'] ?? ''),  // Código do ativo (ex: LCA-25A04157044)
            'produto' => $_GET['produto'] ?? ($_GET['tipo'] ?? ($_GET['Produto'] ?? '')),
            'estrategia' => $_GET['estrategia'] ?? ($_GET['indexador'] ?? ($_GET['Estrategia'] ?? '')),
            'emissor' => $_GET['emissor'] ?? ($_GET['CNPJ'] ?? ($_GET['Emissor'] ?? '')),
            'vencimento' => $_GET['vencimento'] ?? ($_GET['Vencimento'] ?? ''),
            'quantidade' => isset($_GET['quantidade']) ? (int)$_GET['quantidade'] : (isset($_GET['Quantidade']) ? (int)$_GET['Quantidade'] : 0),
            'valor_bruto' => isset($_GET['valor_bruto']) ? $_GET['valor_bruto'] : (isset($_GET['Valor_Bruto']) ? $_GET['Valor_Bruto'] : 0),
            'valor_liquido' => isset($_GET['valor_liquido']) ? $_GET['valor_liquido'] : (isset($_GET['Valor_Liquido']) ? $_GET['Valor_Liquido'] : 0),
            'taxa_emissao' => isset($_GET['taxa_emissao']) ? $_GET['taxa_emissao'] : (isset($_GET['Taxa_Emissao']) ? $_GET['Taxa_Emissao'] : 0)
        ];

        // Preservar a extensão bruta (formato DB) para processamento no servidor e, em seguida, formatar para exibição.
        if (!empty($data['vencimento'])) {
            $data['vencimento_raw'] = $data['vencimento'];
            // aceita AAAA-MM-DD ou YYYY-MM-DD
            $d = $data['vencimento'];
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $d, $m)) {
                $data['vencimento'] = sprintf('%02s/%02s/%02s', $m[3], $m[2], $m[1]);
            }
        } else {
            $data['vencimento_raw'] = '';
        }

        // Valores monetários no banco estão em centavos (ex: 51673367 -> 516733.67).
        // Para exibição converte para reais com duas casas. Também aceita strings/numerics.
        $formatAmount = function($v) {
            if ($v === null || $v === '') return '0,00';
            $num = is_numeric($v) ? (float)$v : floatval(str_replace([',','R$',' '], ['','.',''], $v));
            // assume inteiro em centavos se for maior que 1000 e sem ponto decimal
            if ($num > 1000 && intval($num) == $num) {
                $num = $num / 100.0;
            }
            return number_format($num, 2, ',', '.');
        };

        $data['valor_bruto_display'] = $formatAmount($data['valor_bruto'] ?? $data['Valor_Bruto'] ?? 0);
        $data['valor_liquido_display'] = $formatAmount($data['valor_liquido'] ?? $data['Valor_Liquido'] ?? 0);

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
        $ativo = $_POST['ativo'] ?? '';  // Código específico do ativo (ex: LCA-25A04157044)
        $tipo = $_POST['tipo'] ?? '';
        $estrategia = $_POST['estrategia'] ?? '';
        $quantidade = (float)($_POST['quantidade'] ?? 0);
        $quantidade_negociada = (int)($_POST['quantidade_negociada'] ?? $quantidade);
        
        // Valores do vendedor
        $valor_bruto_saida = (float)($_POST['valor_bruto_saida'] ?? 0);
        $taxa_saida = (float)($_POST['taxa_saida'] ?? 0);
        $valor_liquido_saida = (float)($_POST['valor_liquido_saida'] ?? 0);
        
        // Valores do comprador
        $conta_comprador = $_POST['conta_entrada'] ?? '';
        $nome_comprador = $_POST['nome_entrada'] ?? '';
        $taxa_entrada = (float)($_POST['taxa_entrada'] ?? 0);
        $valor_bruto_entrada = (float)($_POST['valor_bruto_entrada'] ?? 0);
        
        // Valores de assessor
        $valor_plataforma = (float)($_POST['valor_plataforma'] ?? 0);

        // Operador atual
        $auth = new AuthManager();
        $operator = $auth->getCurrentUser();

        // Cálculos
        $corretagem = $valor_bruto_entrada - $valor_bruto_saida;
        $roa = ($valor_bruto_entrada != 0) ? ($corretagem / $valor_bruto_entrada) * 100 : 0;
        
        // Cálculos do vendedor
        $preco_unitario_saida = ($quantidade_negociada > 0) ? ($valor_liquido_saida / $quantidade_negociada) : 0;
        $valor_bruto_importado = (float)($_POST['valor_bruto_importado'] ?? $valor_bruto_saida);
        $ganho_saida = $valor_liquido_saida - ($valor_bruto_importado / 100); // Converte centavos para reais
        $rentabilidade_saida = ($valor_plataforma > 0) ? ($ganho_saida / $valor_plataforma) * 100 : 0;
        
        // Cálculos do comprador
        $preco_unitario_entrada = ($quantidade_negociada > 0) ? ($valor_bruto_entrada / $quantidade_negociada) : 0;
        
        // Cálculos do assessor
        $corretagem_assessor = $corretagem;
        $roa_assessor = $roa;
        
        // Cálculos do retorno ao vendedor
        $retorno_vendedor_valor = $valor_liquido_saida;
        $retorno_vendedor_pct = ($valor_bruto_saida > 0) ? ($valor_liquido_saida / $valor_bruto_saida) * 100 : 0;

        // Persiste a negociação na tabela NEGOCIACOES com todos os detalhes
        require_once dirname(dirname(__DIR__)) . '/app/model/NegociacaoModel.php';
        $negModel = new NegociacaoModel();
        $savedId = $negModel->save([
            'conta_vendedor' => $conta,
            'nome_vendedor' => $cliente,
            'produto' => $tipo,
            'estrategia' => $estrategia,
            'quantidade_negociada' => $quantidade_negociada,
            'valor_bruto_importado_raw' => $valor_bruto_importado,
            'taxa_saida' => $taxa_saida,
            'valor_bruto_saida' => $valor_bruto_saida,
            'valor_liquido_saida' => $valor_liquido_saida,
            'preco_unitario_saida' => $preco_unitario_saida,
            'ganho_saida' => $ganho_saida,
            'rentabilidade_saida' => $rentabilidade_saida,
            'conta_comprador' => $conta_comprador,
            'nome_comprador' => $nome_comprador,
            'taxa_entrada' => $taxa_entrada,
            'valor_bruto_entrada' => $valor_bruto_entrada,
            'preco_unitario_entrada' => $preco_unitario_entrada,
            'valor_plataforma' => $valor_plataforma,
            'corretagem_assessor' => $corretagem_assessor,
            'roa_assessor' => $roa_assessor,
        ]);

        // Após salvar a negociação, copia dados negociados para INTERMEDIACOES_TABLE_NEGOCIADA
        // (decrementando quantidades e filtrando qty > 0)
        try {
            require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacoesNegociadaModel.php';
            $negModel2 = new IntermediacoesNegociadaModel();
            
            $criteria = [
                'conta' => $conta,
                'ativo' => $ativo,
                'produto' => $tipo,
                'emissor' => $_POST['emissor'] ?? null,
                'vencimento' => $_POST['vencimento'] ?? null,
            ];
            
            // Copia registros decrementados para a tabela NEGOCIADA
            $ok = $negModel2->copyNegotiatedRecords($criteria, $quantidade_negociada);
            if (!$ok) {
                error_log("Warning: copyNegotiatedRecords retornou false para negociação {$savedId}");
            }
        } catch (Exception $e) {
            error_log("Exception ao copiar registros negociados: " . $e->getMessage());
        }

        $resultData = [
            'conta' => $conta,
            'cliente' => $cliente,
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'quantidade_negociada' => $quantidade_negociada,
            'valor_bruto_saida' => $valor_bruto_saida,
            'taxa_saida' => $taxa_saida,
            'valor_liquido_saida' => $valor_liquido_saida,
            'valor_bruto_entrada' => $valor_bruto_entrada,
            'taxa_entrada' => $taxa_entrada,
            'corretagem' => $corretagem,
            'roa' => $roa,
            'preco_unitario_saida' => $preco_unitario_saida,
            'ganho_saida' => $ganho_saida,
            'rentabilidade_saida' => $rentabilidade_saida,
            'retorno_vendedor_valor' => $retorno_vendedor_valor,
            'retorno_vendedor_pct' => $retorno_vendedor_pct,
            'operator' => $operator
        ];
        $resultData['saved_id'] = $savedId;

        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/dados/negotiate_result.php';
        include $base_dir . '/includes/footer.php';
    }

    /**
     * Exibe os dados da tabela INTERMEDIACOES_TABLE_NEGOCIADA
     * (registros com quantidades atualizadas pós-negociação, filtrados por qty > 0)
     */
    public function visualizar_negociadas() {
        $base_dir = dirname(dirname(__DIR__));
        
        // Carrega negociações da tabela `NEGOCIACOES` para exibição
        require_once $base_dir . '/app/model/NegociacaoModel.php';
        $negModel = new NegociacaoModel();
        
        $data = $negModel->getAllNegotiations(200);
        
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/dados/ViewNegociadas.php';
        include $base_dir . '/includes/footer.php';
    }
}