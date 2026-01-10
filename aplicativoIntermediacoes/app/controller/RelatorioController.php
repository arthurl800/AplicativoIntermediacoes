<?php
// app/controller/RelatorioController.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';
require_once dirname(dirname(__DIR__)) . '/app/model/AuditoriaModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/AuditLogger.php';

class RelatorioController {
    private $authManager;
    private $auditoriaModel;
    private $auditLogger;

    public function __construct() {
        $this->authManager = new AuthManager();
        $this->auditoriaModel = new AuditoriaModel();
        $this->auditLogger = AuditLogger::getInstance();
        if (!$this->authManager->isLoggedIn()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function auditoria() {
        $base_dir = dirname(dirname(__DIR__));
        $limit = 50;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
        $auditoria = $this->auditoriaModel->getAuditoriaCompleta($limit, $offset);
        
        // Registra visualização do relatório de auditoria
        $this->auditLogger->logView('RELATORIO', 'Visualização do relatório de auditoria de negociações');
        
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/relatorio/Auditoria.php';
        include $base_dir . '/includes/footer.php';
    }
    
    /**
     * Novo método para visualizar auditoria completa do sistema
     */
    public function auditoriaGeral() {
        // Apenas administradores podem acessar
        if (!$this->authManager->isAdmin()) {
            $_SESSION['auth_error'] = "Acesso negado. Apenas administradores.";
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        
        $base_dir = dirname(dirname(__DIR__));
        
        // Filtros
        $filtros = [
            'usuario_id' => isset($_GET['usuario_id']) && !empty($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null,
            'modulo' => isset($_GET['modulo']) && !empty($_GET['modulo']) ? $_GET['modulo'] : null,
            'acao' => isset($_GET['acao']) && !empty($_GET['acao']) ? $_GET['acao'] : null,
            'data_inicio' => isset($_GET['data_inicio']) && !empty($_GET['data_inicio']) ? $_GET['data_inicio'] : null,
            'data_fim' => isset($_GET['data_fim']) && !empty($_GET['data_fim']) ? $_GET['data_fim'] : null,
        ];
        
        // Remove filtros nulos
        $filtros = array_filter($filtros, function($v) { return $v !== null; });
        
        // Paginação
        $limit = 100;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
        
        // Busca logs
        $logs = $this->auditLogger->buscarLogs($filtros, $limit, $offset);
        $totalLogs = $this->auditLogger->contarLogs($filtros);
        $totalPaginas = ceil($totalLogs / $limit);
        
        // Registra visualização
        $this->auditLogger->logView('RELATORIO', 'Visualização da auditoria geral do sistema');
        
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/relatorio/AuditoriaGeral.php';
        include $base_dir . '/includes/footer.php';
    }

    public function exportarCSV() {
        $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
        $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
        
        if (!$this->validarData($dataInicio) || !$this->validarData($dataFim)) {
            die('Datas inválidas');
        }
        
        $negociacoes = $this->getNegociacoesPorPeriodo($dataInicio, $dataFim);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_negociacoes_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        fputcsv($output, array(
            'ID', 'Data Registro', 'Conta Vendedor', 'Nome Vendedor', 'Produto', 'Estrategia',
            'Quantidade Negociada', 'Valor Bruto Saída (R$)', 'Valor Líquido Saída (R$)',
            'Preço Unitário Saída (R$)', 'Ganho Saída (R$)', 'Rentabilidade Saída (%)',
            'Conta Comprador', 'Nome Comprador', 'Taxa Entrada (%)', 'Valor Bruto Entrada (R$)',
            'Preço Unitário Entrada (R$)', 'Corretagem Assessor (R$)', 'ROA Assessor (%)'
        ), ',', '"');
        
        foreach ($negociacoes as $neg) {
            fputcsv($output, array(
                $neg['id'],
                date('d/m/Y H:i:s', strtotime($neg['Data_Registro'])),
                $neg['Conta_Vendedor'],
                $neg['Nome_Vendedor'],
                $neg['Produto'],
                $neg['Estrategia'],
                number_format($neg['Quantidade_negociada'], 0, ',', '.'),
                number_format($neg['Valor_Bruto_Saida'], 2, ',', '.'),
                number_format($neg['Valor_Liquido_Saida'], 2, ',', '.'),
                number_format(isset($neg['Preco_Unitario_Saida']) ? $neg['Preco_Unitario_Saida'] : 0, 4, ',', '.'),
                number_format(isset($neg['Ganho_Saida']) ? $neg['Ganho_Saida'] : 0, 2, ',', '.'),
                number_format(isset($neg['Rentabilidade_Saida']) ? $neg['Rentabilidade_Saida'] : 0, 2, ',', '.'),
                $neg['Conta_Comprador'],
                $neg['Nome_Comprador'],
                number_format($neg['Taxa_Entrada'], 2, ',', '.'),
                number_format($neg['Valor_Bruto_Entrada'], 2, ',', '.'),
                number_format(isset($neg['Preco_Unitario_Entrada']) ? $neg['Preco_Unitario_Entrada'] : 0, 4, ',', '.'),
                number_format(isset($neg['Corretagem_Assessor']) ? $neg['Corretagem_Assessor'] : 0, 2, ',', '.'),
                number_format(isset($neg['Roa_Assessor']) ? $neg['Roa_Assessor'] : 0, 2, ',', '.')
            ), ',', '"');
        }
        
        fclose($output);
        exit;
    }

    private function getNegociacoesPorPeriodo($dataInicio, $dataFim) {
        $pdo = Database::getInstance()->getConnection();
        try {
            $sql = "SELECT * FROM NEGOCIACOES WHERE DATE(Data_Registro) BETWEEN :data_inicio AND :data_fim ORDER BY Data_Registro DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':data_inicio', $dataInicio);
            $stmt->bindValue(':data_fim', $dataFim);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar negociações: " . $e->getMessage());
            return array();
        }
    }

    private function validarData($data) {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) && strtotime($data) !== false;
    }
}
