<?php
// app/model/IntermediacaoModel.php

require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class IntermediacaoModel {
    private $pdo;
    private $tableName = 'INTERMEDIACOES';

    public function __construct() {
        // Inicializa a conexão PDO
        try {
            $this->pdo = Database::getInstance()->getConnection();
        } catch (PDOException $e) {
            // Em caso de falha na conexão, relança a exceção
            throw new Exception("Falha ao inicializar o modelo de intermediações: " . $e->getMessage());
        }
    }

    /**
     * Retorna lista de colunas da tabela INTERMEDIACOES.
     * @return array
     */
    public function getAvailableColumns(): array {
        try {
            $stmt = $this->pdo->prepare("PRAGMA table_info({$this->tableName})");
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $names = array_map(fn($c) => $c['name'], $cols);
            return $names;
        } catch (PDOException $e) {
            error_log("Erro ao obter colunas da tabela: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna os dados selecionando apenas as colunas fornecidas.
     * Se $columns estiver vazio, retorna getAllData().
     * @param array $columns
     * @return array
     */
    public function getDataWithColumns(array $columns): array {
        if (empty($columns)) {
            return $this->getAllData();
        }

        // Define mapeamentos entre nomes solicitados e colunas reais do DB
        $mapping = [
            'Conta' => 'Codigo_Cliente',
            'Nome' => 'Nome_Corretora',
            'Produto' => 'Ativo',
            'Estrategia' => 'Tipo_Operacao',
            'Emissor' => 'CNPJ',
            'Vencimento' => null, // sem coluna direta
            'Taxa_Compra' => 'Taxa_Liquidacao',
            'Quantidade' => 'Quantidade',
            'Valor_Bruto' => 'Valor_Bruto',
            'IOF' => null, // sem coluna direta
            'IR' => 'IRRF',
            'Valor_Liquido' => 'Valor_Liquido',
            'Data_Compra' => 'Data'
        ];

        // Aliases solicitados pelo usuário
        $aliases = [
            'Taxa_Compra' => 'TX',
            'Quantidade' => 'QTD',
            'Valor_Bruto' => 'VB',
            'Valor_Liquido' => 'VL',
            'Data_Compra' => 'Data'
        ];

        $available = $this->getAvailableColumns();
        $selectParts = [];

        foreach ($columns as $req) {
            $req = trim($req);
            if ($req === '') continue;

            $actual = $mapping[$req] ?? null;
            $alias = $aliases[$req] ?? $req;

            if ($actual && in_array($actual, $available)) {
                // Safe: actual column exists in table
                $selectParts[] = "{$actual} AS \"{$alias}\"";
            } else {
                // Coluna mapeada não existe — retorna NULL para manter posição/nome
                $selectParts[] = "NULL AS \"{$alias}\"";
            }
        }

        if (empty($selectParts)) {
            return [];
        }

        $colsSql = implode(', ', $selectParts);

        try {
            $sql = "SELECT {$colsSql} FROM {$this->tableName} LIMIT 100";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados com colunas especificadas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca todos os dados de intermediação (limitado a 100).
     * @return array
     */
    public function getAllData(): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} LIMIT 100");
            $stmt->execute();
            // Retorna o resultado como um array associativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Loga o erro em ambiente de produção ou exibe para debug
            error_log("Erro ao buscar todos os dados de intermediação: " . $e->getMessage());
            return []; // Retorna array vazio em caso de falha
        }
    }
    
    /**
     * Busca dados de intermediação com base em filtros.
     * @param array $filters Array associativo com chaves: mercado, sub_mercado, ativo.
     * @return array
     */
    public function getFilteredData(array $filters): array {
        $where = [];
        $params = [];
        $sql = "SELECT * FROM {$this->tableName}";

        if (!empty($filters['mercado'])) {
            $where[] = "Mercado LIKE :mercado";
            $params[':mercado'] = '%' . $filters['mercado'] . '%';
        }

        if (!empty($filters['sub_mercado'])) {
            $where[] = "Sub_Mercado LIKE :sub_mercado";
            $params[':sub_mercado'] = '%' . $filters['sub_mercado'] . '%';
        }
        
        if (!empty($filters['ativo'])) {
            $where[] = "Ativo LIKE :ativo";
            $params[':ativo'] = '%' . $filters['ativo'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " LIMIT 100";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            // Retorna o resultado como um array associativo
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados filtrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insere um lote de registros no banco de dados.
     * (Mantido para a funcionalidade de upload)
     * @param array $records Array de arrays de dados.
     * @return array Resultado do processamento (número de inseridos, erros).
     */
    public function insertBatch(array $records): array {
        $insertedCount = 0;
        $errors = [];
        $this->pdo->beginTransaction();

        // Lista das 23 colunas esperadas (ajustar conforme sua estrutura de DB)
        $columns = [
            'Data', 'Mercado', 'Sub_Mercado', 'Tipo_Operacao', 'Ativo', 
            'CNPJ', 'Quantidade', 'Preco_Unitario', 'Valor_Bruto', 
            'Taxa_Liquidacao', 'Taxa_Emolumentos', 'ISS', 'IRRF', 
            'Outras_Despesas', 'Valor_Liquido', 'Corretagem', 
            'Nome_Corretora', 'Codigo_Cliente', 'Descricao_Ativo',
            'Custo_Operacional', 'Custo_Financ', 'Ajuste_Op', 'Total_Operacao'
        ];
        
        // Cria a string de placeholders para o INSERT (ex: ?, ?, ?, ...)
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnNames = implode(', ', $columns);

        $sql = "INSERT INTO {$this->tableName} ({$columnNames}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($records as $index => $row) {
                // Assumindo que $row já está na ordem correta das colunas
                if (count($row) === count($columns)) {
                    // Prepara os valores para o INSERT
                    $values = [];
                    foreach ($row as $value) {
                        // Converte valores vazios/nulos para null do SQL
                        $values[] = ($value === '' || $value === null) ? null : $value;
                    }

                    if (!$stmt->execute($values)) {
                        $errors[] = "Erro ao inserir linha " . ($index + 1);
                    } else {
                        $insertedCount++;
                    }
                } else {
                    $errors[] = "Linha " . ($index + 1) . ": número incorreto de colunas (" . count($row) . " de " . count($columns) . " esperadas).";
                }
            }

            $this->pdo->commit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $errors[] = "Erro fatal de transação: " . $e->getMessage();
        }

        return ['inserted' => $insertedCount, 'errors' => $errors];
    }
}
