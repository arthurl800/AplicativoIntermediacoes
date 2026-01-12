<?php
// app/model/AuditoriaModel.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class AuditoriaModel {
    private $pdo;

    public function __construct() {
        // Define o timezone para o horário de Brasília (GMT-3)
        date_default_timezone_set('America/Sao_Paulo');
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retorna estatísticas por operador (quem negociou o quê).
     * @return array
     */
    public function getEstatisticasPorOperador(): array {
        try {
            $sql = "SELECT * FROM VW_NEGOCIACOES_POR_OPERADOR";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas por operador: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna estatísticas por produto.
     * @return array
     */
    public function getEstatisticasPorProduto(): array {
        try {
            $sql = "SELECT * FROM VW_NEGOCIACOES_POR_PRODUTO";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas por produto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna estatísticas por data (diário).
     * @param int $dias Últimos N dias a retornar
     * @return array
     */
    public function getEstatisticasPorData(int $dias = 30): array {
        try {
            $sql = "SELECT * FROM VW_NEGOCIACOES_POR_DATA 
                   WHERE data_negociacao >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                   ORDER BY data_negociacao DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas por data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna resumo executivo.
     * @return array|null
     */
    public function getResumoExecutivo(): ?array {
        try {
            $sql = "SELECT * FROM VW_RESUMO_EXECUTIVO_NEGOCIACOES";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar resumo executivo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna histórico de auditoria de uma negociação específica.
     * @param int $negociacaoId
     * @return array
     */
    public function getAuditoriaParaNegociacao(int $negociacaoId): array {
        try {
            $sql = "SELECT * FROM NEGOCIACOES_AUDITORIA 
                   WHERE negociacao_id = :id
                   ORDER BY data_acao DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $negociacaoId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar auditoria da negociação: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna todo o histórico de auditoria (paginado).
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAuditoriaCompleta(int $limit = 100, int $offset = 0): array {
        try {
            $sql = "SELECT na.*, u.name AS usuario_name 
                   FROM NEGOCIACOES_AUDITORIA na
                   LEFT JOIN USUARIOS_TABLE u ON na.usuario_name = u.Nome
                   ORDER BY na.data_acao DESC
                   LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar auditoria completa: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna estatísticas de negociações em um período específico.
     * @param string $dataInicio (YYYY-MM-DD)
     * @param string $dataFim (YYYY-MM-DD)
     * @return array|null
     */
    public function getEstatisticasPorPeriodo(string $dataInicio, string $dataFim): ?array {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_negociacoes,
                        SUM(Quantidade_negociada) AS quantidade_total,
                        SUM(Valor_Bruto_Saida) AS valor_saida_total,
                        SUM(Valor_Liquido_Saida) AS valor_liquido_total,
                        SUM(Corretagem_Assessor) AS corretagem_total,
                        AVG(Roa_Assessor) AS roa_medio,
                        AVG(Rentabilidade_Saida) AS rentabilidade_media
                    FROM NEGOCIACOES
                    WHERE DATE(Data_Registro) BETWEEN :data_inicio AND :data_fim";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':data_inicio', $dataInicio);
            $stmt->bindValue(':data_fim', $dataFim);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas por período: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra uma ação de auditoria na tabela NEGOCIACOES_AUDITORIA
     * @param int $negociacaoId ID da negociação
     * @param string $acao Tipo de ação (CRIACAO, ATUALIZACAO, EXCLUSAO, etc)
     * @param string|null $usuarioName Nome do usuário que realizou a ação
     * @param string|null $usuarioIp IP do usuário
     * @param array|null $dadosAntes Estado anterior dos dados
     * @param array|null $dadosDepois Estado posterior dos dados
     * @param string|null $descricaoMudanca Descrição da mudança realizada
     * @return bool
     */
    public function registrarAuditoria(
        int $negociacaoId,
        string $acao,
        ?string $usuarioName = null,
        ?string $usuarioIp = null,
        ?array $dadosAntes = null,
        ?array $dadosDepois = null,
        ?string $descricaoMudanca = null
    ): bool {
        try {
            $sql = "INSERT INTO NEGOCIACOES_AUDITORIA 
                    (negociacao_id, acao, usuario_name, usuario_ip, dados_antes, dados_depois, descricao_mudanca, data_hora) 
                    VALUES 
                    (:negociacao_id, :acao, :usuario_name, :usuario_ip, :dados_antes, :dados_depois, :descricao_mudanca, :data_hora)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':negociacao_id', $negociacaoId, PDO::PARAM_INT);
            $stmt->bindValue(':acao', $acao);
            $stmt->bindValue(':usuario_name', $usuarioName);
            $stmt->bindValue(':usuario_ip', $usuarioIp);
            $stmt->bindValue(':dados_antes', $dadosAntes ? json_encode($dadosAntes, JSON_UNESCAPED_UNICODE) : null);
            $stmt->bindValue(':dados_depois', $dadosDepois ? json_encode($dadosDepois, JSON_UNESCAPED_UNICODE) : null);
            $stmt->bindValue(':descricao_mudanca', $descricaoMudanca);
            $stmt->bindValue(':data_hora', date('Y-m-d H:i:s'));
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
            return false;
        }
    }
}
