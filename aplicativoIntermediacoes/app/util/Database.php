<?php
// app/util/Database.php - Classe Singleton para Conexão PDO

class Database {
    private static $instance = null;
    private $pdo;

    // Configuração de Conexão (SQLite para simplicidade em ambiente de teste/local)
    // O arquivo do banco será criado na raiz do projeto como 'app_data.db'
    private $db_path = __DIR__ . '/../../app_data.db';

    // Config de MySQL (opcional)
    private $useMysql = false;
    private $mysqlConfig = [];

    private function __construct() {
        // Tenta conectar ao banco de dados
        try {
            // Tenta carregar configuração em config/database.php (opcional)
            $configFile = dirname(dirname(__DIR__)) . '/config/database.php';
            if (file_exists($configFile)) {
                $cfg = include $configFile;
                if (is_array($cfg) && !empty($cfg['DB_HOST'] ?? null)) {
                    $this->useMysql = true;
                    $this->mysqlConfig = $cfg;
                }
            }

            if ($this->useMysql) {
                // Conexão MySQL usando config/database.php
                $host = $this->mysqlConfig['DB_HOST'] ?? 'localhost';
                $db   = $this->mysqlConfig['DB_NAME'] ?? '';
                $user = $this->mysqlConfig['DB_USER'] ?? '';
                $pass = $this->mysqlConfig['DB_PASS'] ?? '';
                $charset = $this->mysqlConfig['DB_CHARSET'] ?? 'utf8mb4';

                $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
                $this->pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                // Se conectar, garante que as tabelas existem (MySQL)
                $this->initializeDatabase(true);
                return;
            }

            // Verifica/garante que o diretório do DB exista e seja gravável
            $dbDir = dirname($this->db_path);

            if (!is_dir($dbDir)) {
                // Tenta criar o diretório (se permissões permitirem)
                @mkdir($dbDir, 0755, true);
            }

            if (!is_dir($dbDir) || !is_writable($dbDir)) {
                throw new Exception("Diretório do DB '{$dbDir}' não é gravável pelo processo PHP. Verifique permissões do diretório e do usuário do processo web. Path: {$this->db_path}");
            }

            // Se o arquivo ainda não existe, tenta criá-lo (touch)
            if (!file_exists($this->db_path)) {
                if (@touch($this->db_path) === false) {
                    throw new Exception("Falha ao criar o arquivo de banco de dados em '{$this->db_path}'. Verifique permissões do diretório '{$dbDir}'.");
                }
            }

            // Conexão SQLite
            $this->pdo = new PDO("sqlite:{$this->db_path}");

            // Configurações importantes
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

          // Se o arquivo do DB não existe ou está vazio, cria as tabelas
          if (!file_exists($this->db_path) || filesize($this->db_path) === 0) {
              $this->initializeDatabase(false);
          }
            
        } catch (PDOException $e) {
            // Erros vindos do PDO (ex.: unable to open database file)
            error_log("Database Connection Error: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Detalhe: " . $e->getMessage() . "\nSugestão: verifique se o arquivo '{$this->db_path}' existe e se o processo PHP tem permissão de leitura/escrita.");
        } catch (Exception $e) {
            // Erros de setup (permissões/criação de diretório)
            error_log("Database Setup Error: " . $e->getMessage());
            die("Erro ao configurar o banco de dados: " . $e->getMessage() . "\nSugestão: verifique permissões do diretório e arquivo app_data.db (ex.: chown -R www-data:www-data . ; chmod 755 .)");
        }
    }

    /**
     * Inicializa a estrutura básica do banco de dados (tabelas USERS e INTERMEDIACOES).
     */
    /**
     * Inicializa a estrutura básica do banco de dados (tabelas USERS e INTERMEDIACOES).
     * @param bool $mysql true se estamos inicializando em MySQL
     */
    private function initializeDatabase(bool $mysql = false) {
        // Tabela USERS (para o AuthManager)
        if ($mysql) {
            // MySQL-compatible schema
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS USERS (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(191) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                cpf VARCHAR(32) UNIQUE NOT NULL,
                role VARCHAR(32) NOT NULL DEFAULT 'user'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
            // Detecta coluna de senha existente e usa-a para inserir o admin
            try {
                $pwCol = null;
                $stmt = $this->pdo->prepare("SHOW COLUMNS FROM USERS");
                $stmt->execute();
                $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($cols as $c) {
                    $field = $c['Field'] ?? ($c['field'] ?? null);
                    if ($field === 'password' || $field === 'password_hash') {
                        $pwCol = $field;
                        break;
                    }
                }
                if ($pwCol) {
                    $sql = "INSERT IGNORE INTO USERS (username, {$pwCol}, cpf, role) VALUES (:u, :p, :cpf, :role)";
                    $i = $this->pdo->prepare($sql);
                    $i->execute([':u' => 'admin', ':p' => $hashed_password, ':cpf' => '00000000000', ':role' => 'admin']);
                }
            } catch (Exception $e) {
                // Se não for possível detectar/executar, não interrompe a inicialização
            }

            $this->pdo->exec("CREATE TABLE IF NOT EXISTS INTERMEDIACOES (
                id INT AUTO_INCREMENT PRIMARY KEY,
                Data DATE,
                Mercado TEXT,
                Sub_Mercado TEXT,
                Tipo_Operacao TEXT,
                Ativo TEXT,
                CNPJ TEXT,
                Quantidade INT,
                Preco_Unitario DOUBLE,
                Valor_Bruto DOUBLE,
                Taxa_Liquidacao DOUBLE,
                Taxa_Emolumentos DOUBLE,
                ISS DOUBLE,
                IRRF DOUBLE,
                Outras_Despesas DOUBLE,
                Valor_Liquido DOUBLE,
                Corretagem DOUBLE,
                Nome_Corretora TEXT,
                Codigo_Cliente TEXT,
                Descricao_Ativo TEXT,
                Custo_Operacional DOUBLE,
                Custo_Financ DOUBLE,
                Ajuste_Op DOUBLE,
                Total_Operacao DOUBLE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } else {
            // SQLite schema (legacy)
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
