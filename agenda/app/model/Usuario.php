<?php

include_once 'Database.php';

class Usuario
{
    private $id;
    private $nome;
    private $email;
    private $senha;

    public function __construct($id, $nome, $email, $senha = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
    }

    // Métodos de acesso (getters)
    public function getId()
    {
        return $this->id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getEmail()
    {
        return $this->email;
    }

    // Método para cadastro de novo usuário
    public function create()
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        // Criptografa a senha antes de armazenar
        $hashedPassword = password_hash($this->senha, PASSWORD_DEFAULT);
        //$hashedPassword = $this->senha;

        $stmt = $conn->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $this->nome, $this->email, $hashedPassword);

        if ($stmt->execute()) {
            $stmt->close();
            $db->closeConnection();
            return true;
        } else {
            $stmt->close();
            $db->closeConnection();
            return false;
        }
    }

    // Método para autenticar usuário
    public static function authenticate($email, $senha)
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            //if ($senha == $user['senha']){
            // Verifica a senha criptografada
            if (password_verify($senha, $user['senha'])) {
                // Autenticação bem-sucedida
                $conn->close();
                return new Usuario($user['id'], $user['nome'], $user['email']);
            }
        }

        $conn->close();
        return false; // Falha na autenticação
    }

    // Método para buscar usuário por ID
    public static function getById($id)
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $conn->close();
            return new Usuario($row['id'], $row['nome'], $row['email']);
        }

        $conn->close();
        return null;
    }
}
