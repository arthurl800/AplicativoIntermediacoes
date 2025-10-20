<?php
// app/util/Database.php

// Inclui o arquivo de configuração para acessar as constantes (DB_HOST, DB_USER, etc.)
require_once dirname(__DIR__, 2) . '/config/database.php'; 

class Database {
    private static $instance = null;
    private $connection;

    /**
     * O construtor é privado para impedir a criação direta de objetos (padrão Singleton).
     */
    private function __construct() {
        // Cria a string DSN (Data Source Name) para a conexão MySQL
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        // Opções de conexão:
        $options = [
            // Garante que as senhas, se usadas, não sejam armazenadas em cache.
            PDO::ATTR_PERSISTENT => false, 
            
            // 💡 PONTO CRÍTICO: Configura o PDO para lançar exceções em caso de erros SQL.
            // Isso permite que o try/catch no UserModel capture e registre a falha.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            
            // Define o modo de busca padrão como array associativo.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        try {
            // Tenta estabelecer a conexão PDO
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Em caso de falha na conexão (credenciais erradas, servidor fora do ar, etc.)
            // Registra o erro no log do servidor
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Interrompe a execução e exibe uma mensagem de erro genérica (ou mais específica 
            // em ambiente de desenvolvimento) para o usuário.
            die("<h1>Erro de Conexão com o Banco de Dados</h1><p>Não foi possível estabelecer a conexão com o banco de dados. Verifique o arquivo de configuração (`config/database.php`).</p><p>Detalhe: " . $e->getMessage() . "</p>");
        }
    }

    /**
     * Retorna a instância única da classe (padrão Singleton).
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna o objeto de conexão PDO.
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    // Impede a clonagem e a desserialização do objeto para manter o Singleton
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}