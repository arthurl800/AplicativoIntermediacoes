<?php
// app/controller/AdminController.php

require_once dirname(dirname(__DIR__)) . '/app/model/UserModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';

class AdminController {
    private $userModel;
    private $authManager;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->authManager = new AuthManager();

        // Garante que apenas administradores possam acessar este Controller
        if (!$this->authManager->isAdmin()) {
            $_SESSION['auth_error'] = "Acesso negado. Você não tem permissão de administrador.";
            AuthManager::redirectTo('index.php?controller=dashboard&action=index');
        }
    }

    // Ação para listar todos os usuários
    public function users() {
        // Busca todos os usuários no banco de dados
        $users = $this->userModel->findAll();

        // Recupera e limpa a mensagem de sucesso/erro da sessão
        $message = $_SESSION['admin_message'] ?? null;
        unset($_SESSION['admin_message']);

        // Carrega as views
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_list.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Ação para deletar um usuário (somente via POST)
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            $_SESSION['admin_message'] = "Erro: Requisição inválida.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        $idToDelete = (int)$_POST['id'];
        $currentUser = $this->authManager->getCurrentUser();
        
        // Proteção: Não permite que o administrador delete a si mesmo
        if ($currentUser && $currentUser['id'] == $idToDelete) {
            $_SESSION['admin_message'] = "Erro: Você não pode deletar a sua própria conta.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }
        
        // Tenta deletar o usuário
        if ($this->userModel->delete($idToDelete)) {
            $_SESSION['admin_message'] = "Usuário ID {$idToDelete} removido com sucesso.";
        } else {
            $_SESSION['admin_message'] = "Erro ao tentar remover o usuário ID {$idToDelete}.";
        }

        AuthManager::redirectTo('index.php?controller=admin&action=users');
    }

    /**
     * Exibe o formulário de edição de usuário (somente GET).
     */
    public function editUser() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['admin_message'] = "Usuário inválido.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['admin_message'] = "Usuário não encontrado.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_edit.php';
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    /**
     * Processa o envio do formulário de edição de usuário.
     */
    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            $_SESSION['admin_message'] = "Requisição inválida.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        $id = (int)$_POST['id'];
        $username = trim($_POST['username'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $password = $_POST['password'] ?? null;

        if ($username === '' || $cpf === '') {
            $_SESSION['admin_message'] = "Nome de usuário e CPF são obrigatórios.";
            AuthManager::redirectTo('index.php?controller=admin&action=editUser&id=' . $id);
        }

        $ok = $this->userModel->update($id, $username, $password, $cpf, $role);
        if ($ok) {
            $_SESSION['admin_message'] = "Usuário atualizado com sucesso.";
        } else {
            $_SESSION['admin_message'] = "Falha ao atualizar usuário.";
        }

        AuthManager::redirectTo('index.php?controller=admin&action=users');
    }

    /**
     * Exibe o formulário para adicionar um novo usuário (apenas admin).
     */
    public function addUser() {
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_add.php';
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    /**
     * Processa a criação de um novo usuário pelo admin.
     */
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['admin_message'] = 'Requisição inválida.';
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        $username = trim($_POST['username'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($username === '' || $cpf === '' || $password === '' || $password !== $confirm) {
            $_SESSION['admin_message'] = 'Preencha todos os campos corretamente.';
            AuthManager::redirectTo('index.php?controller=admin&action=addUser');
        }

        $ok = $this->userModel->create($username, $password, $cpf, $role);
        if ($ok) {
            $_SESSION['admin_message'] = 'Usuário criado com sucesso.';
        } else {
            $_SESSION['admin_message'] = 'Falha ao criar usuário. Verifique logs.';
        }

        AuthManager::redirectTo('index.php?controller=admin&action=users');
    }
}
