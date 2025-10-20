<?php
// app/model/IntermediacaoModel.php
// Certifique-se de que o utilitário de conexão seja incluído
require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class IntermediacaoModel {
    private $pdo;
    private $tableName;

    public function __construct() {
        $dbConfig = require dirname(dirname(__DIR__)) . '/config/database.php';
        $this->tableName = $dbConfig['TABLE_NAME'];
        // Obtém a conexão PDO do utilitário
        $this->pdo = Database::getInstance()->getConnection(); 
    }

    /**
     * Insere uma lista de registros (linhas CSV) no banco.
     */
    public function insertBatch(array $records): array {
        $total_inserted = 0;
        $errors = [];

        $sql = "INSERT INTO {$this->tableName} (
            Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor, 
            Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade, 
            Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio, 
            Data_Registro, Data_Cotizacao_Prev, Tipo_Plano, ID_Registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);

        // Uso de Transação para garantir a integridade e performance
        $this->pdo->beginTransaction();
        try {
            foreach ($records as $data) {
                // Mapeamento e Formatação (ex: conversão de tipos, tratamento de campos vazios)
                $bind_params = [
                    $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], 
                    empty($data[8]) ? null : $data[8],  
                    empty($data[9]) ? null : (float)$data[9],  
                    empty($data[10]) ? null : (float)$data[10], 
                    empty($data[11]) ? null : $data[11], 
                    empty($data[12]) ? null : (int)$data[12],  
                    empty($data[13]) ? null : (float)$data[13], 
                    empty($data[14]) ? null : (float)$data[14], 
                    empty($data[15]) ? null : (float)$data[15], 
                    empty($data[16]) ? null : (float)$data[16], 
                    $data[17], $data[18], 
                    empty($data[19]) ? null : $data[19], 
                    $data[20], $data[21], $data[22]
                ];
                
                try {
                    $stmt->execute($bind_params);
                    $total_inserted++;
                } catch (\PDOException $e) {
                    // Captura erros (ex: ID_Registro duplicado) e continua
                    $errors[] = "Erro (Linha ID {$data[22]}): " . $e->getMessage();
                }
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $errors[] = "Erro Crítico de Transação: " . $e->getMessage();
        }

        return ['inserted' => $total_inserted, 'errors' => $errors];
    }
}
