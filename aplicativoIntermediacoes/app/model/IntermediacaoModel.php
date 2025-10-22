<?php
// app/model/IntermediacaoModel.php

require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class IntermediacaoModel {
    private $pdo;
    private $tableName;

    public function __construct() {
        // Inicializa a conexão PDO e obtém o nome da tabela do config
        try {
            $this->pdo = Database::getInstance()->getConnection();
            $configFile = dirname(dirname(__DIR__)) . '/config/database.php';
            $cfg = file_exists($configFile) ? include $configFile : [];
            $this->tableName = $cfg['TABLE_NAME'] ?? 'INTERMEDIACOES';
        } catch (PDOException $e) {
            throw new Exception("Falha ao inicializar o modelo de intermediações: " . $e->getMessage());
        }
    }

    /**
     * Retorna lista de colunas da tabela INTERMEDIACOES.
     * @return array
     */
    public function getAvailableColumns(): array {
        try {
            // MySQL: use SHOW COLUMNS
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM {$this->tableName}");
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $names = array_map(fn($c) => $c['Field'] ?? $c['field'] ?? null, $cols);
            return array_values(array_filter($names));
        } catch (PDOException $e) {
            error_log("Erro ao obter colunas da tabela: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Garante que a coluna imported_at exista na tabela (adiciona se necessário).
     */
    private function ensureImportedAtColumnExists(): void {
        $available = $this->getAvailableColumns();
        if (in_array('imported_at', $available)) {
            return;
        }
        try {
            $sql = "ALTER TABLE {$this->tableName} ADD COLUMN imported_at DATETIME NULL";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Não foi possível adicionar coluna imported_at: " . $e->getMessage());
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
     * Retorna uma tabela agregada de investimentos negociáveis por cliente.
     * Agrupa por: Codigo_Cliente (Conta), Nome_Corretora (Cliente), Ativo (Tipo),
     * Tipo_Operacao (Indexador), CNPJ (Emissor), Vencimento (se existir),
     * e Taxa_Plataforma (usa Taxa_Emolumentos ou Taxa_Liquidacao se existir).
     * Calcula SUM(Quantidade) e SUM(Valor_Bruto) por grupo.
     * @param int $limit
     * @return array
     */
    public function getNegotiableAggregates(int $limit = 200): array {
        $available = $this->getAvailableColumns();

        // Colunas mapeadas
        $conta = in_array('Codigo_Cliente', $available) ? 'Codigo_Cliente' : null;
        $cliente = in_array('Nome_Corretora', $available) ? 'Nome_Corretora' : null;
        $tipo = in_array('Ativo', $available) ? 'Ativo' : null;
        $indexador = in_array('Tipo_Operacao', $available) ? 'Tipo_Operacao' : null;
        $emissor = in_array('CNPJ', $available) ? 'CNPJ' : null;
        $vencimento = in_array('Vencimento', $available) ? 'Vencimento' : null;
        // Taxa_Plataforma: prefere Taxa_Emolumentos, senão Taxa_Liquidacao
        $taxa_plat = in_array('Taxa_Emolumentos', $available) ? 'Taxa_Emolumentos' : (in_array('Taxa_Liquidacao', $available) ? 'Taxa_Liquidacao' : null);

        $groupParts = [];
        $selectParts = [];

        if ($conta) { $groupParts[] = $conta; $selectParts[] = "{$conta} AS Conta"; }
        if ($cliente) { $groupParts[] = $cliente; $selectParts[] = "{$cliente} AS Cliente"; }
        if ($tipo) { $groupParts[] = $tipo; $selectParts[] = "{$tipo} AS Tipo"; }
        if ($indexador) { $groupParts[] = $indexador; $selectParts[] = "{$indexador} AS Indexador"; }
        if ($emissor) { $groupParts[] = $emissor; $selectParts[] = "{$emissor} AS Emissor"; }
        if ($vencimento) { $groupParts[] = $vencimento; $selectParts[] = "{$vencimento} AS Vencimento"; }
        if ($taxa_plat) { $groupParts[] = $taxa_plat; $selectParts[] = "{$taxa_plat} AS Taxa_Plataforma"; }

        // Sempre soma quantidade e valor bruto
        $selectParts[] = "SUM(Quantidade) AS Quantidade";
        $selectParts[] = "SUM(Valor_Bruto) AS Valor_Bruto";

        if (empty($selectParts) || empty($groupParts)) {
            return [];
        }

        $selectSql = implode(', ', $selectParts);
        $groupSql = implode(', ', $groupParts);

        try {
            $orderCol = $cliente ?? $conta ?? null;
            $orderClause = $orderCol ? "ORDER BY {$orderCol} ASC" : "";
            $sql = "SELECT {$selectSql} FROM {$this->tableName} GROUP BY {$groupSql} {$orderClause} LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar agregados negociáveis: " . $e->getMessage());
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

        // Lista das colunas esperadas para a nova estrutura
        $columns = [
            'Conta', 'Nome', 'Mercado', 'Sub_Mercado', 'Ativo',
            'Produto', 'CNPJ', 'Emissor', 'Data_Compra', 'Taxa_Compra',
            'Taxa_Emissao', 'Vencimento', 'Quantidade', 'Valor_Bruto',
            'IR', 'IOF', 'Valor_Liquido', 'Estrategia', 'Escritorio',
            'Data_Registro', 'Data_Cotizacao_Prev', 'Tipo_Plano', 
            'ID_Registro'
        ];

        // Garante que a tabela tenha a coluna imported_at
        $this->ensureImportedAtColumnExists();
        $available = $this->getAvailableColumns();
        if (in_array('imported_at', $available)) {
            $columns[] = 'imported_at';
        }
        
        // Cria a string de placeholders para o INSERT (ex: ?, ?, ?, ...)
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnNames = implode(', ', $columns);

        $sql = "INSERT INTO {$this->tableName} ({$columnNames}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);

            try {
                $this->pdo->beginTransaction();
                
                foreach ($records as $index => $row) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = ($value === '' || $value === null) ? null : $value;
                    }

                    if (in_array('Data_Importacao', $available)) {
                        $values[] = date('Y-m-d H:i:s');
                    }

                    if (count($values) !== count($columns)) {
                        $errors[] = "Linha " . ($index + 1) . ": número incorreto de colunas (" . count($values) . " de " . count($columns) . " esperadas).";
                        continue;
                    }

                    if (!$stmt->execute($values)) {
                        throw new PDOException("Erro ao inserir linha " . ($index + 1) . ": " . implode(' | ', $stmt->errorInfo()));
                    }
                    $insertedCount++;
                }

                $this->pdo->commit();
            } catch (PDOException $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                $errors[] = "Erro na transação: " . $e->getMessage();

            }
        } catch (PDOException $e) {
            $errors[] = "Erro na preparação do SQL: " . $e->getMessage();
        }

        return ['inserted' => $insertedCount, 'errors' => $errors];
    }
}
