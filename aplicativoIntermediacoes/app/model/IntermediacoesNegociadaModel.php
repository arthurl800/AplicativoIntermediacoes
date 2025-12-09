<?php
// app/model/IntermediacoesNegociadaModel.php

require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class IntermediacoesNegociadaModel {
    private $pdo;
    private $sourceTable = 'INTERMEDIACOES_TABLE';
    private $targetTable = 'INTERMEDIACOES_TABLE_NEGOCIADA';

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Copia os registros de INTERMEDIACOES_TABLE para INTERMEDIACOES_TABLE_NEGOCIADA
     * (com quantidades atualizadas conforme as negociações).
     * Filtra apenas registros com Quantidade > 0.
     * 
     * @param array $criteria Critérios opcionais para filtrar qual registro foi negociado
     *        chaves: conta, ativo, produto, emissor, vencimento
     * @param int $quantidadeNegociada quantidade que foi negociada (decrementada)
     * @return bool true se a cópia foi bem-sucedida
     */
    public function copyNegotiatedRecords(array $criteria = [], int $quantidadeNegociada = 0): bool {
        try {
            $this->pdo->beginTransaction();

            // Se critérios foram fornecidos, primeiro decrementa os registros de INTERMEDIACOES_TABLE
            if (!empty($criteria)) {
                $this->decrementSourceRecords($criteria, $quantidadeNegociada);
            }

            // Cópia de INTERMEDIACOES_TABLE para INTERMEDIACOES_TABLE_NEGOCIADA (com Quantidade > 0)
            $copySql = "INSERT INTO {$this->targetTable} (
                Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor,
                Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade,
                Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio,
                Data_Registro, Data_Cotizacao_Prev, Tipo_Plano, ID_Registro, Data_Importacao
            ) SELECT
                Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor,
                Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade,
                Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio,
                Data_Registro, Data_Cotizacao_Prev, Tipo_Plano, ID_Registro, NOW()
            FROM {$this->sourceTable}
            WHERE Quantidade > 0
            AND ID_Registro NOT IN (
                SELECT ID_Registro FROM {$this->targetTable}
            )";

            $stmt = $this->pdo->prepare($copySql);
            $stmt->execute();

            $this->pdo->commit();
            error_log("IntermediacoesNegociadaModel::copyNegotiatedRecords() - Registros copiados com sucesso");
            return true;

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Erro ao copiar registros negociados: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrementa quantidade em INTERMEDIACOES_TABLE para os registros que correspondem aos critérios.
     * @param array $criteria conta, ativo, produto, emissor, vencimento
     * @param int $quantidadeNegociada quantidade a decrementar
     */
    private function decrementSourceRecords(array $criteria, int $quantidadeNegociada): void {
        if ($quantidadeNegociada <= 0) return;

        $where = [];
        $params = [];

        if (!empty($criteria['conta'])) {
            $where[] = "Conta = :conta";
            $params[':conta'] = $criteria['conta'];
        }
        if (!empty($criteria['ativo'])) {
            $where[] = "Ativo = :ativo";
            $params[':ativo'] = $criteria['ativo'];
        }
        if (!empty($criteria['produto'])) {
            $where[] = "Produto = :produto";
            $params[':produto'] = $criteria['produto'];
        }
        if (!empty($criteria['emissor'])) {
            $where[] = "Emissor = :emissor";
            $params[':emissor'] = $criteria['emissor'];
        }
        if (!empty($criteria['vencimento'])) {
            $where[] = "Vencimento = :vencimento";
            $params[':vencimento'] = $criteria['vencimento'];
        }

        if (empty($where)) {
            error_log("decrementSourceRecords: nenhum critério fornecido");
            return;
        }

        $whereSql = implode(' AND ', $where);
        $decrementSql = "UPDATE {$this->sourceTable} 
                        SET Quantidade = Quantidade - :qty
                        WHERE {$whereSql} AND Quantidade > 0
                        ORDER BY Data_Compra ASC
                        LIMIT 1";

        $params[':qty'] = $quantidadeNegociada;
        $stmt = $this->pdo->prepare($decrementSql);
        $stmt->execute($params);

        error_log("decrementSourceRecords: linha atualizada para critérios " . json_encode($criteria));
    }

    /**
     * Retorna todos os registros de INTERMEDIACOES_TABLE_NEGOCIADA (com Quantidade > 0).
     * @param int $limit Limite de registros a retornar
     * @return array
     */
    public function getAllNegotiated(int $limit = 100): array {
        try {
            $sql = "SELECT * FROM {$this->targetTable} WHERE Quantidade > 0 ORDER BY Data_Importacao DESC LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar registros negociados: " . $e->getMessage());
            return [];
        }
    }
}
