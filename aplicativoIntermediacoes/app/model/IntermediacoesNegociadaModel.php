<?php
// app/model/IntermediacoesNegociadaModel.php

// Inclui dependências
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
     *        chaves: conta, ativo, produto, emissor, vencimento, source_id (priorizado)
     * @param int $quantidadeNegociada quantidade que foi negociada (decrementada)
     * @return bool true se a cópia foi bem-sucedida
     */
    public function copyNegotiatedRecords(array $criteria = [], int $quantidadeNegociada = 0): bool {
        try {
            $this->pdo->beginTransaction();
            // Só tenta a transferência quando for fornecido pelo menos um critério significativo.
            if ((count(array_filter($criteria)) > 0 || isset($criteria['source_id'])) && $quantidadeNegociada > 0) {
                // Transfere a quantidade negociada dos registros fonte para a tabela destino.
                // Isso irá inserir linhas na tabela destino representando as quantidades vendidas,
                // mesmo que a linha fonte fique com 0 depois.
                $this->transferNegotiatedQuantity($criteria, $quantidadeNegociada);
            } else {
                // Se nenhum critério específico for fornecido, o sistema recorrerá à cópia de quaisquer registros positivos restantes
                // que ainda não estejam presentes na tabela de destino (comportamento legado).
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
            }

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
     * Transfere a quantidade negociada do(s) registro(s) fonte para a tabela negociada.
     * Para cada registro fonte encontrado (ordenado por Data_Compra asc) consome parte
     * ou a totalidade da quantidade disponível e insere um registro correspondente na tabela
     * de negociadas com a quantidade efetivamente negociada e valores proporcionais.
     *
     * @param array $criteria Pode incluir 'source_id' (id do registro) para localização direta
     * @param int $quantidadeNegociada
     * @return void
     */
    private function transferNegotiatedQuantity(array $criteria, int $quantidadeNegociada): void {
        if ($quantidadeNegociada <= 0) return;

        $where = [];
        $params = [];

        // Se source_id foi fornecido, usar como critério principal (mais específico)
        if (!empty($criteria['source_id'])) {
            $where[] = "id = :source_id";
            $params[':source_id'] = $criteria['source_id'];
        } else {
            // Caso contrário, construir where a partir dos outros critérios (menos específico)
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
        }

        if (empty($where)) {
            error_log("transferNegotiatedQuantity: nenhum critério fornecido");
            return;
        }

        $whereSql = implode(' AND ', $where) . ' AND Quantidade > 0';

        // Seleciona os registros fonte que podem suprir a quantidade negociada
        $selectSql = "SELECT * FROM {$this->sourceTable} WHERE {$whereSql} ORDER BY Data_Compra ASC";
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->execute($params);

        $remaining = $quantidadeNegociada;

        $updateSql = "UPDATE {$this->sourceTable} SET Quantidade = :newqty WHERE id = :id";
        $updateStmt = $this->pdo->prepare($updateSql);

        $insertSql = "INSERT INTO {$this->targetTable} (
            Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor,
            Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade,
            Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio,
            Data_Registro, Data_Cotizacao_Prev, Tipo_Plano, ID_Registro, Data_Importacao
        ) VALUES (
            :Conta, :Nome, :Mercado, :Sub_Mercado, :Ativo, :Produto, :CNPJ, :Emissor,
            :Data_Compra, :Taxa_Compra, :Taxa_Emissao, :Vencimento, :Quantidade,
            :Valor_Bruto, :IR, :IOF, :Valor_Liquido, :Estrategia, :Escritorio,
            :Data_Registro, :Data_Cotizacao_Prev, :Tipo_Plano, :ID_Registro, NOW()
        )";
        $insertStmt = $this->pdo->prepare($insertSql);

        while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($remaining <= 0) break;
            $origQty = (float)$row['Quantidade'];
            if ($origQty <= 0) continue;

            $take = min($origQty, $remaining);

            // calcula valores proporcionais ao montante tomado
            $factor = ($origQty > 0) ? ($take / $origQty) : 0;
            $valor_bruto_taken = isset($row['Valor_Bruto']) ? ((float)$row['Valor_Bruto'] * $factor) : 0.0;
            $ir_taken = isset($row['IR']) ? ((float)$row['IR'] * $factor) : 0.0;
            $iof_taken = isset($row['IOF']) ? ((float)$row['IOF'] * $factor) : 0.0;
            $valor_liquido_taken = isset($row['Valor_Liquido']) ? ((float)$row['Valor_Liquido'] * $factor) : 0.0;

            // Atualiza quantidade no registro fonte
            $newQty = $origQty - $take;
            $updateStmt->execute([':newqty' => $newQty, ':id' => $row['id']]);

            // Insere o registro negociado (apenas a parcela negociada)
            $insertParams = [
                ':Conta' => $row['Conta'],
                ':Nome' => $row['Nome'],
                ':Mercado' => $row['Mercado'],
                ':Sub_Mercado' => $row['Sub_Mercado'],
                ':Ativo' => $row['Ativo'],
                ':Produto' => $row['Produto'],
                ':CNPJ' => $row['CNPJ'],
                ':Emissor' => $row['Emissor'],
                ':Data_Compra' => $row['Data_Compra'],
                ':Taxa_Compra' => $row['Taxa_Compra'],
                ':Taxa_Emissao' => $row['Taxa_Emissao'],
                ':Vencimento' => $row['Vencimento'],
                ':Quantidade' => $take,
                ':Valor_Bruto' => $valor_bruto_taken,
                ':IR' => $ir_taken,
                ':IOF' => $iof_taken,
                ':Valor_Liquido' => $valor_liquido_taken,
                ':Estrategia' => $row['Estrategia'],
                ':Escritorio' => $row['Escritorio'],
                ':Data_Registro' => $row['Data_Registro'],
                ':Data_Cotizacao_Prev' => $row['Data_Cotizacao_Prev'],
                ':Tipo_Plano' => $row['Tipo_Plano'],
                ':ID_Registro' => $row['ID_Registro'],
            ];

            $insertStmt->execute($insertParams);

            $remaining -= $take;
        }

        if ($remaining > 0) {
            error_log("transferNegotiatedQuantity: quantidade solicitada não totalmente atendida. restante={$remaining}");
        }
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
