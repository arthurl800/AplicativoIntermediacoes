<?php
// app/model/UserModel.php

// MUDANÇA: Corrigindo o caminho de inclusão para garantir que Database seja encontrado
// O Database está em /app/util/Database.php
require_once dirname(__DIR__) . '/util/Database.php'; 

class UserModel {
    private $db;
    private $table = 'USERS';
    private $passwordColumn = 'password';

    public function __construct() {
        // Obtém a instância única da conexão PDO
        $this->db = Database::getInstance()->getConnection(); 

        // Detecta qual coluna de senha existe na tabela USERS (password_hash ou password)
        try {
            // Detecta driver para escolher a forma correta de inspecionar colunas
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $colNames = [];

            if ($driver === 'sqlite') {
                $stmt = $this->db->prepare("PRAGMA table_info({$this->table})");
                $stmt->execute();
                $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $colNames = array_map(fn($c) => $c['name'], $cols);
            } elseif ($driver === 'mysql') {
                // SHOW COLUMNS returns Field as column name
                $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table}");
                $stmt->execute();
                $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $colNames = array_map(fn($c) => $c['Field'] ?? ($c['field'] ?? null), $cols);
            } else {
                // Generic fallback: try PRAGMA then show columns
                try {
                    $stmt = $this->db->prepare("PRAGMA table_info({$this->table})");
                    $stmt->execute();
                    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $colNames = array_map(fn($c) => $c['name'], $cols);
                } catch (Exception $ex) {
                    // ignore
                }
            }

            if (in_array('password_hash', $colNames)) {
                $this->passwordColumn = 'password_hash';
            } elseif (in_array('password', $colNames)) {
                $this->passwordColumn = 'password';
            } else {
                // Fallback
                $this->passwordColumn = 'password';
            }
        } catch (Exception $e) {
            // Se algo falhar, mantém o fallback 'password'
            $this->passwordColumn = 'password';
        }
    }

    /**
     * Cria um novo usuário no banco de dados.
     * @param string $username
     * @param string $password
     * @param string $cpf
     * @param string $role
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function create(string $username, string $password, string $cpf, string $role = 'user'): bool {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Usa a coluna detectada para armazenar o hash
    $pwCol = $this->passwordColumn;
    $sql = "INSERT INTO {$this->table} (username, {$pwCol}, cpf, role) VALUES (:username, :password_hash, :cpf, :role)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':username' => $username,
                ':password_hash' => $passwordHash,
                ':cpf' => $cpf,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            // Loga a mensagem de erro real no log do servidor
            error_log("PDO EXCEPTION: Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca um usuário pelo nome de usuário.
     * @param string $username
     * @return array|null Dados do usuário ou null se não encontrado.
     */
    public function findByUsername(string $username): ?array {
        // Seleciona a coluna correta detectada e garante que o resultado contenha a chave 'password'
        $pwCol = $this->passwordColumn;
        $sql = "SELECT id, username, {$pwCol} as password, cpf, role FROM {$this->table} WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Garante compatibilidade: AuthController espera $user['password']
        if ($user && isset($user[$pwCol]) && !isset($user['password'])) {
            $user['password'] = $user[$pwCol];
        }
        
        return $user ?: null;
    }

    /**
     * Retorna todos os usuários (usado pelo AdminController).
     * @return array Lista de todos os usuários.
     */
    public function findAll(): array {
        $sql = "SELECT id, username, cpf, role FROM {$this->table} ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Deleta um usuário pelo ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return false;
        }
    }
}