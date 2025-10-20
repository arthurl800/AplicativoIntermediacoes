<?php


include_once 'Database.php';

class Empresa
{

    private $id;
    private $nome;
    private $cnpj;
    private $endereco;
    private $telefone;


    function __construct($id, $nome, $cnpj, $endereco, $telefone)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->cnpj = $cnpj;
        $this->endereco = $endereco;
        $this->telefone = $telefone;
    }

    //Getter e Setter

    function getId()
    {
        return $this->id;
    }

    function getNome()
    {
        return $this->nome;
    }

    function getCnpj()
    {
        return $this->cnpj;
    }

    function getEndereco()
    {
        return $this->endereco;
    }

    function getTelefone()
    {
        return $this->telefone;
    }

    function setId($id)
    {
        $this->id  = $id;
    }

    function setNome($nome)
    {
        $this->nome  = $nome;
    }

    function setCnpj($cnpj)
    {
        $this->cnpj  = $cnpj;
    }

    function setEndereco($endereco)
    {
        $this->endereco  = $endereco;
    }

    function setTelefone($telefone)
    {
        $this->telefone  = $telefone;
    }

    // Método para incluir empresa no BD
    function create()
    {

        // Obtém a conexão com o BD
        $db = Database::getInstance();
        $conn = $db->connect();

        // Preparar a consulta SQL
        $stmt = $conn->prepare("INSERT INTO empresa (nome, cnpj, endereco, telefone) VALUES (?,?,?,?)");
        echo $this->telefone;
        $stmt->bind_param("ssss", $this->nome, $this->cnpj, $this->endereco, $this->telefone);

        // Executa consulta

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

    // Método para atualizar empresa no BD
    function update()
    {
        // Obtém a conexão com o BD
        $db = Database::getInstance();
        $conn = $db->connect();

        // Preparar a consulta SQL
        $stmt = $conn->prepare("UPDATE empresa set nome = ?, cnpj = ?, endereco = ?, telefone = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $this->nome, $this->cnpj, $this->endereco, $this->telefone, $this->id);

        // Executa consulta
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

    // Método para apagar empresa no BD
    function delete()
    {
        // Obtém a conexão com o BD
        $db = Database::getInstance();
        $conn = $db->connect();

        // Preparar a consulta SQL
        $stmt = $conn->prepare("DELETE FROM empresa WHERE id = ?");
        $stmt->bind_param("i", $this->id);

        // Executa consulta

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


    static function getById($id)
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM empresa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $conn->close();
            return new Empresa($row['id'], $row['nome'], $row['cnpj'], $row['endereco'], $row['telefone']);
        }
        $conn->close();
        return null;
    }

    static function getAll()
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        $query = "SELECT * FROM empresa";
        $result = $conn->query($query);

        $empresas = [];

        while ($row = $result->fetch_assoc()) {
            $empresas[] = new Empresa($row['id'], $row['nome'], $row['cnpj'], $row['endereco'], $row['telefone']);
        }

        $conn->close();
        return $empresas;
    }


    static function searchByName($query)
    {
        $db = Database::getInstance();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM empresa WHERE nome LIKE ?");
        // Adiciona os caracteres de wildcard para o LIKE
        $name = '%' . $query . '%'; 
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();

        $empresas = [];
        while ($row = $result->fetch_assoc()) {
            $empresas[] = new Empresa($row['id'], $row['nome'], $row['cnpj'], $row['endereco'], $row['telefone']);
        }

        $conn->close();
        return $empresas;
    }
}
