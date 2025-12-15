<?php
// app/util/Database.php - Classe Singleton para Conexão PDO

class Database {
    private static $instance = null;
    private $pdo;

    // Configuração de Conexão
    // O arquivo do banco será criado na raiz do projeto como 'app_data.db'
    private function __construct() {
        try {
            // Utiliza a classe Config para ler as variáveis de ambiente
            $host = Config::dbHost();
            $db   = Config::dbName();
            $user = Config::dbUser();
            $pass = Config::dbPassword();
            $port = Config::get('DB_PORT', 3306); // Porta padrão do MySQL
            $driver = Config::get('DB_DRIVER', 'mysql'); // 'mysql' ou 'pgsql'

            $dsn = '';
            if ($driver === 'mysql') {
                // DSN para MySQL
                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            } elseif ($driver === 'pgsql') {
                // DSN para PostgreSQL
                $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
            } else {
                throw new Exception("Driver de banco de dados '{$driver}' não suportado.");
            }

            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Detalhe: " . $e->getMessage());
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
     * Retorna o objeto PDO.
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }
}
