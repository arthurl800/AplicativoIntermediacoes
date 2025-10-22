<?php
// app/model/IntermediacaoModel.php

declare(strict_types=1);

// Importa a classe de utilitário de banco de dados
require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class IntermediacaoModel {
    private PDO $pdo;
    private string $tableName;

    /**
     * Construtor: Inicializa a conexão PDO e obtém o nome da tabela.
     * @throws Exception Se houver falha na inicialização da conexão ou modelo.
     */
    public function __construct() {
        try {
            // Inicializa a conexão PDO através do Singleton da classe Database
            $this->pdo = Database::getInstance()->getConnection();
            
            // Tenta obter o nome da tabela do config
            $configFile = dirname(dirname(__DIR__)) . '/config/database.php';
            $cfg = file_exists($configFile) ? include $configFile : [];
            $this->tableName = $cfg['TABLE_NAME'] ?? 'INTERMEDIACOES';
        } catch (PDOException $e) {
            error_log("Falha ao inicializar o modelo de intermediações: " . $e->getMessage());
            throw new Exception("Falha ao inicializar o modelo de intermediações.");
        }
    }

    /**
     * Retorna o nome da tabela.
     * @return string
     */
    private function getTableName(): string {
        return $this->tableName;
    }

    /**
     * Retorna lista de colunas disponíveis na tabela.
     * @return array<string>
     */
    public function getAvailableColumns(): array {
        try {
            // Assume MySQL: usa SHOW COLUMNS. Ajustar se o SGBD for diferente.
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM {$this->getTableName()}");
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mapeia para extrair apenas o nome da coluna ('Field' ou 'field')
            $names = array_map(fn($c) => $c['Field'] ?? $c['field'] ?? null, $cols);
            return array_values(array_filter($names));
        } catch (PDOException $e) {
            error_log("Erro ao obter colunas da tabela '{$this->getTableName()}': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Garante que a coluna 'imported_at' exista na tabela (adiciona se necessário).
     */
    private function ensureImportedAtColumnExists(): void {
        $available = $this->getAvailableColumns();
        if (in_array('imported_at', $available, true)) {
            return;
        }
        try {
            // Adiciona a coluna se não existir
            $sql = "ALTER TABLE {$this->getTableName()} ADD COLUMN imported_at DATETIME NULL";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Não foi possível adicionar coluna 'imported_at': " . $e->getMessage());
        }
    }

    /**
     * Retorna os dados selecionando apenas as colunas fornecidas.
     * Se $columns estiver vazio, retorna getAllData().
     * @param array $columns Nomes das colunas solicitadas (chaves do $mapping).
     * @return array
     */
    public function getDataWithColumns(array $columns): array {
        if (empty($columns)) {
            return $this->getAllData();
        }

        // Mapeamento entre nomes solicitados (View) e colunas reais do DB
        $mapping = [
            'Conta' => 'Codigo_Cliente',
            'Nome' => 'Nome_Corretora',
            'Produto' => 'Ativo',
            'Estrategia' => 'Tipo_Operacao',
            'Emissor' => 'CNPJ',
            'Vencimento' => null, // Coluna que pode não existir ou ser tratada como NULL
            'Taxa_Compra' => 'Taxa_Liquidacao',
            'Quantidade' => 'Quantidade',
            'Valor_Bruto' => 'Valor_Bruto',
            'IOF' => null, // Coluna que pode não existir ou ser tratada como NULL
            'IR' => 'IRRF',
            'Valor_Liquido' => 'Valor_Liquido',
            'Data_Compra' => 'Data'
        ];

        // Aliases solicitados pelo usuário para o output
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

            if ($actual && in_array($actual, $available, true)) {
                // Coluna real existe na tabela
                $selectParts[] = "{$actual} AS \"{$alias}\"";
            } else {
                // Coluna não mapeada ou mapeada para coluna inexistente - Retorna NULL
                $selectParts[] = "NULL AS \"{$alias}\"";
            }
        }

        if (empty($selectParts)) {
            return [];
        }

        $colsSql = implode(', ', $selectParts);

        try {
            $sql = "SELECT {$colsSql} FROM {$this->getTableName()} LIMIT 100";
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
            $sql = "SELECT * FROM {$this->getTableName()} LIMIT 100";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os dados de intermediação: " . $e->getMessage());
            return [];
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
        $sql = "SELECT * FROM {$this->getTableName()}";

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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados filtrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna uma tabela agregada de investimentos negociáveis por cliente.
     * Agrupa por: Conta, Nome, Produto, Estrategia, Emissor, Vencimento.
     * Calcula SUM para Valores e Quantidade, e MAX para Taxas e Datas.
     * @param int $limit Limite de registros a retornar.
     * @return array
     */
   public function getNegotiableAggregates(int $limit = 200): array {
    $available = $this->getAvailableColumns();

    // Colunas mapeadas conforme seu banco real
    $conta = in_array('Conta', $available) ? 'Conta' : null;
    $nome = in_array('Nome', $available) ? 'Nome' : null;
    $tipo = in_array('Produto', $available) ? 'Produto' : null;
    $estrategia = in_array('Estrategia', $available) ? 'Estrategia' : null;
    $emissor = in_array('Emissor', $available) ? 'Emissor' : null;
    $vencimento = in_array('Vencimento', $available) ? 'Vencimento' : null;
    $taxaEmissao = in_array('Taxa_Emissao', $available) ? 'Taxa_Emissao' : null;

    // Monta partes dinâmicas
    $groupParts = [];
    $selectParts = [];

    if ($conta) { $groupParts[] = $conta; $selectParts[] = "{$conta} AS Conta"; }
    if ($nome) { $groupParts[] = $nome; $selectParts[] = "{$nome} AS Nome"; }
    if ($tipo) { $groupParts[] = $tipo; $selectParts[] = "{$tipo} AS Produto"; }
    if ($estrategia) { $groupParts[] = $estrategia; $selectParts[] = "{$estrategia} AS Estrategia"; }
    if ($emissor) { $groupParts[] = $emissor; $selectParts[] = "{$emissor} AS Emissor"; }
    if ($vencimento) { $groupParts[] = $vencimento; $selectParts[] = "{$vencimento} AS Vencimento"; }
    if ($taxaEmissao) { $groupParts[] = $taxaEmissao; $selectParts[] = "{$taxaEmissao} AS Taxa_Emissao"; }

    // Campos de soma obrigatórios
    $selectParts[] = "SUM(Quantidade) AS Quantidade";
    $selectParts[] = "SUM(Valor_Bruto) AS Valor_Bruto";
    $selectParts[] = "SUM(IR) AS IR";
    $selectParts[] = "SUM(IOF) AS IOF";
    $selectParts[] = "SUM(Valor_Liquido) AS Valor_Liquido";

    // Datas relevantes
    if (in_array('Data_Compra', $available)) {
        $selectParts[] = "MIN(Data_Compra) AS Data_Compra";
    }

    // Garante que há algo para agrupar
    if (empty($selectParts) || empty($groupParts)) {
        return [];
    }

    $selectSql = implode(', ', $selectParts);
    $groupSql = implode(', ', $groupParts);

    try {
        $orderCol = $nome ?? $conta ?? $tipo ?? null;
        $orderClause = $orderCol ? "ORDER BY {$orderCol} ASC" : "";
        $sql = "SELECT {$selectSql} FROM {$this->tableName} GROUP BY {$groupSql} {$orderClause} LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔎 Log opcional para verificar saída
        error_log("DEBUG getNegotiableAggregates -> registros encontrados: " . count($data));

        return $data;
    } catch (PDOException $e) {
        error_log("Erro ao buscar agregados negociáveis: " . $e->getMessage());
        return [];
    }
}

    /**
     * Insere um lote de registros no banco de dados.
     * @param array $records Array de arrays de dados.
     * @return array Resultado do processamento (número de inseridos, erros).
     */
    public function insertBatch(array $records): array {
        $insertedCount = 0;
        $errors = [];
        $tableName = $this->getTableName();

        // 1. Garante que a tabela tenha a coluna imported_at
        $this->ensureImportedAtColumnExists();
        $available = $this->getAvailableColumns();
        $shouldInsertImportedAt = in_array('imported_at', $available, true);
        
        // 2. Colunas esperadas para o INSERT
        $columns = [
            'Conta', 'Nome', 'Mercado', 'Sub_Mercado', 'Ativo',
            'Produto', 'CNPJ', 'Emissor', 'Data_Compra', 'Taxa_Compra',
            'Taxa_Emissao', 'Vencimento', 'Quantidade', 'Valor_Bruto',
            'IR', 'IOF', 'Valor_Liquido', 'Estrategia', 'Escritorio',
            'Data_Registro', 'Data_Cotizacao_Prev', 'Tipo_Plano', 
            'ID_Registro'
        ];

        // 3. Adiciona 'imported_at' à lista de colunas se existir
        if ($shouldInsertImportedAt) {
            $columns[] = 'imported_at';
        }
        
        // 4. Prepara o SQL
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnNames = implode(', ', $columns);

        $sql = "INSERT INTO {$tableName} ({$columnNames}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);

            try {
                $this->pdo->beginTransaction();
                
                $now = date('Y-m-d H:i:s');

                foreach ($records as $index => $row) {
                    $values = [];
                    // Processa os valores da linha
                    foreach ($row as $value) {
                        $values[] = ($value === '' || $value === null) ? null : $value;
                    }
                    
                    // Adiciona o timestamp 'imported_at' se a coluna estiver sendo inserida
                    if ($shouldInsertImportedAt) {
                        $values[] = $now;
                    }

                    if (count($values) !== count($columns)) {
                        $errors[] = "Linha " . ($index + 1) . ": número incorreto de colunas (" . count($values) . " de " . count($columns) . " esperadas).";
                        continue;
                    }

                    if (!$stmt->execute($values)) {
                        // Captura o erro específico do statement
                        $errorInfo = $stmt->errorInfo();
                        throw new PDOException("Erro ao inserir linha " . ($index + 1) . ": Código SQLSTATE {$errorInfo[0]} - Driver Error: {$errorInfo[1]} - Message: {$errorInfo[2]}");
                    }
                    $insertedCount++;
                }

                $this->pdo->commit();
            } catch (PDOException $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                $errors[] = "Erro na transação de inserção: " . $e->getMessage();

            }
        } catch (PDOException $e) {
            $errors[] = "Erro na preparação do SQL para inserção: " . $e->getMessage();
        }

        return ['inserted' => $insertedCount, 'errors' => $errors];
    }
}
