<?php
// app/model/UserModel.php

// O Database está em /app/util/Database.php
require_once dirname(__DIR__) . '/util/Database.php'; 

class UserModel {
    private $db;
    private $table;
    private $passwordColumn = 'Senha';

    public function __construct() {
        // Obtém a instância única da conexão PDO e o nome da tabela do config
        $this->db = Database::getInstance()->getConnection();
        $cfg = include dirname(dirname(__DIR__)) . '/config/database.php';
        $this->table = $cfg['USER_TABLE'];
    }

    /**
     * Cria um novo usuário no banco de dados.
     * @param string $username
     * @param string $password
     * @param string $cpf
     * @param string $role
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function create(string $username, string $password, ?string $cpf = null, string $role = 'user'): bool {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO {$this->table} (Nome, Senha, CPF, Funcao) VALUES (:username, :password_hash, :cpf, :role)";
        
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
        $sql = "SELECT id, Nome as username, Senha as password, CPF as cpf, Funcao as role FROM {$this->table} WHERE Nome = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }

    /**
     * Retorna todos os usuários (usado pelo AdminController).
     * @return array Lista de todos os usuários.
     */
    public function findAll(): array {
        $sql = "SELECT id, Nome as username, CPF as cpf, Funcao as role FROM {$this->table} ORDER BY id ASC";
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

    /**
     * Busca usuário por ID.
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array {
        $sql = "SELECT id, Nome as username, CPF as cpf, Funcao as role FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Atualiza os dados de um usuário. Se $password for fornecida, atualiza também a senha.
     * @param int $id
     * @param string $username
     * @param string|null $password
     * @param string|null $cpf
     * @param string|null $role
     * @return bool
     */
    public function update(int $id, string $username, ?string $password = null, ?string $cpf = null, ?string $role = null): bool {
        $parts = [];
        $params = [':id' => $id, ':username' => $username];

        $parts[] = "Nome = :username";
        if (!is_null($cpf)) { $parts[] = "CPF = :cpf"; $params[':cpf'] = $cpf; }
        if (!is_null($role)) { $parts[] = "Funcao = :role"; $params[':role'] = $role; }
        if (!is_null($password) && $password !== '') {
            $parts[] = "Senha = :senha";
            $params[':senha'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $parts) . " WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
}