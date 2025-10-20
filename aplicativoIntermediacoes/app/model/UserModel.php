<?php
// app/model/UserModel.php

// MUDANÇA: Corrigindo o caminho de inclusão para garantir que Database seja encontrado
// O Database está em /app/util/Database.php
require_once dirname(__DIR__) . '/util/Database.php'; 

class UserModel {
    private $db;
    private $table = 'USERS';

    public function __construct() {
        // Obtém a instância única da conexão PDO
        $this->db = Database::getInstance()->getConnection(); 
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
        
        // CORREÇÃO AQUI: Trocando 'password' por 'password_hash' na query
        $sql = "INSERT INTO {$this->table} (username, password_hash, cpf, role) VALUES (:username, :password_hash, :cpf, :role)";
        
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
        // CORREÇÃO AQUI: Trocando 'password' por 'password_hash' na query
        // A coluna 'password_hash' é retornada e o AuthController usa 'password_verify' nela
        $sql = "SELECT id, username, password_hash, cpf, role FROM {$this->table} WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        
        // O PDO lança a PDOException aqui se a coluna for desconhecida
        $user = $stmt->fetch();
        
        // Se o usuário foi encontrado, o nome da coluna no array $user será 'password_hash'.
        // Precisamos renomear a chave para 'password' para que o AuthController não quebre,
        // já que ele espera $user['password'] no método processLogin.
        if ($user && isset($user['password_hash'])) {
            $user['password'] = $user['password_hash'];
            unset($user['password_hash']);
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