<?php
// app/model/UserModel.php

// CORREÇÃO DE CAMINHO: Garantindo que ele aponte corretamente para /config/Database.php
// (__DIR__ é /app/model/, sobe dois níveis para a raiz, desce para /config/)
require_once __DIR__ . '/../../config/Database.php'; 

class UserModel {
    private $db;
    private $table = 'USERS';

    public function __construct() {
        // Agora, 'new Database()' funcionará, pois a classe Database está definida no arquivo incluído
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Busca um usuário pelo ID.
     * @param int $id
     * @return array|false
     */
    public function findById(int $id) {
        try {
            $query = "SELECT id, username, cpf, role FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um usuário pelo nome de usuário.
     * Inclui o campo de senha (password_hash) para fins de login.
     * @param string $username
     * @return array|false
     */
    public function findByUsername(string $username) {
        try {
            // CORREÇÃO ESSENCIAL: Garante que a coluna de senha esteja correta (password_hash)
            $query = "SELECT id, username, cpf, role, password_hash 
                      FROM " . $this->table . " 
                      WHERE username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por nome: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna todos os usuários (excluindo o password_hash).
     * @return array
     */
    public function findAll() {
        try {
            $query = "SELECT id, username, cpf, role FROM " . $this->table . " ORDER BY username ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cria um novo usuário.
     * @param string $username
     * @param string $password
     * @param string $cpf
     * @param string $role
     * @return bool
     */
    public function create(string $username, string $password, string $cpf, string $role): bool {
        try {
            // CORREÇÃO: Insere no campo correto (password_hash)
            $query = "INSERT INTO " . $this->table . " (username, password_hash, cpf, role) 
                      VALUES (:username, :password_hash, :cpf, :role)";
            
            $stmt = $this->db->prepare($query);

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':role', $role);
            
            return $stmt->execute();

        } catch (PDOException $e) {
            // Loga o erro, geralmente por falha de UNIQUE KEY (usuário/cpf duplicado)
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados de um usuário existente.
     * @param int $id
     * @param string $username
     * string $cpf
     * @param string $role
     * @param string $password (opcional)
     * @return bool
     */
    public function update(int $id, string $username, string $cpf, string $role, string $password = ''): bool {
        try {
            $query = "UPDATE " . $this->table . " SET username = :username, cpf = :cpf, role = :role";
            $params = [
                ':id' => $id,
                ':username' => $username,
                ':cpf' => $cpf,
                ':role' => $role
            ];

            // Se uma nova senha foi fornecida, atualiza o hash
            if (!empty($password)) {
                // CORREÇÃO: Atualiza o campo correto (password_hash)
                $query .= ", password_hash = :password_hash";
                $params[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta um usuário pelo ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return false;
        }
    }
}
