<?php
// app/util/Database.php - Classe Singleton para Conexão PDO

class Database {
    private static $instance = null;
    private $pdo;

    // Configuração de Conexão (SQLite para simplicidade em ambiente de teste/local)
    // O arquivo do banco será criado na raiz do projeto como 'app_data.db'
    private $db_path = __DIR__ . '/../../app_data.db';

    private function __construct() {
        // Tenta conectar ao banco de dados
        try {
            // Conexão SQLite
            $this->pdo = new PDO("sqlite:{$this->db_path}");
            
            // Configurações importantes
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Se o arquivo do DB não existe ou está vazio, cria as tabelas
            if (!file_exists($this->db_path) || filesize($this->db_path) === 0) {
                 $this->initializeDatabase();
            }
            
        } catch (PDOException $e) {
            // Em caso de erro fatal na conexão
            error_log("Database Connection Error: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Verifique o arquivo Database.php. Detalhe: " . $e->getMessage());
        }
    }

    /**
     * Inicializa a estrutura básica do banco de dados (tabelas USERS e INTERMEDIACOES).
     */
    private function initializeDatabase() {
        // Tabela USERS (para o AuthManager)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS USERS (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            cpf TEXT UNIQUE NOT NULL,
            role TEXT NOT NULL DEFAULT 'user'
        )");
        
        // Insere um usuário administrador padrão (senha: admin)
        $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
        $this->pdo->exec("INSERT OR IGNORE INTO USERS (username, password, cpf, role) VALUES ('admin', '{$hashed_password}', '00000000000', 'admin')");
        
        // Tabela INTERMEDIACOES (para os dados do projeto)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS INTERMEDIACOES (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            Data DATE,
            Mercado TEXT,
            Sub_Mercado TEXT,
            Tipo_Operacao TEXT,
            Ativo TEXT,
            CNPJ TEXT,
            Quantidade INTEGER,
            Preco_Unitario REAL,
            Valor_Bruto REAL,
            Taxa_Liquidacao REAL,
            Taxa_Emolumentos REAL,
            ISS REAL,
            IRRF REAL,
            Outras_Despesas REAL,
            Valor_Liquido REAL,
            Corretagem REAL,
            Nome_Corretora TEXT,
            Codigo_Cliente TEXT,
            Descricao_Ativo TEXT,
            Custo_Operacional REAL,
            Custo_Financ REAL,
            Ajuste_Op REAL,
            Total_Operacao REAL
        )");
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
