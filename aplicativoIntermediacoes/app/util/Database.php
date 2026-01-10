<?php
// app/util/Database.php - Classe Singleton para Conexão PDO

class Database {
    private static $instance = null;
    private $pdo;

    // Configuração de Conexão
    // O arquivo do banco será criado na raiz do projeto como 'app_data.db'
    private function __construct() {
        // Conecta apenas via MySQL usando config/database.php
        try {
            $configFile = dirname(dirname(__DIR__)) . '/config/database.php';
            if (!file_exists($configFile)) {
                throw new Exception("Arquivo de configuração do banco não encontrado: {$configFile}");
            }
            $cfg = include $configFile;
            $host = $cfg['DB_HOST'] ?? 'localhost';
            $db   = $cfg['DB_NAME'] ?? '';
            $user = $cfg['DB_USER'] ?? '';
            $pass = $cfg['DB_PASS'] ?? '%$#';
            $charset = $cfg['DB_CHARSET'] ?? 'utf8';

            // Utiliza TCP/IP ao invés de socket para melhor compatibilidade
            $actualHost = ($host === 'localhost') ? '127.0.0.1' : $host;
            $dsn = "mysql:host={$actualHost};dbname={$db};charset={$charset}";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Erro de conexão com o banco de dados MySQL. Detalhe: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Database Setup Error: " . $e->getMessage());
            die("Erro ao configurar o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Retorna a instância única da classe Database.
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reseta a instância do Singleton (útil para testes ou reconfiguração)
     */
    public static function resetInstance(): void {
        self::$instance = null;
    }

    /**
     * Retorna o objeto PDO.
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }
}
