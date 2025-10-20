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
        $users = $this->userModel->findAll();

        $message = $_SESSION['admin_message'] ?? null;
        unset($_SESSION['admin_message']);

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        // View que precisa de links para criar e editar
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_list.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Ação para exibir o formulário de criação de novo usuário
    public function createUser() {
        // Inicializa dados vazios para o formulário
        $user = ['id' => null, 'username' => '', 'cpf' => '', 'role' => 'user'];
        $title = "Cadastrar Novo Usuário";
        
        $message = $_SESSION['admin_message'] ?? null;
        unset($_SESSION['admin_message']);

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        // Reutilizamos a view de formulário 
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_form.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Ação para processar a criação de novo usuário
    public function processCreateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=admin&action=createUser');
        }

        $username = trim($_POST['username'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? ''; // Nova variável para confirmação
        $role = $_POST['role'] ?? 'user';

        // 1. Validações básicas
        if (empty($username) || empty($cpf) || empty($password) || empty($confirmPassword)) {
            $_SESSION['admin_message'] = "Erro: Nome de usuário, CPF, senha e confirmação de senha são obrigatórios.";
            AuthManager::redirectTo('index.php?controller=admin&action=createUser');
        }
        if (strlen($cpf) !== 11) {
             $_SESSION['admin_message'] = "Erro: O CPF deve conter 11 dígitos.";
            AuthManager::redirectTo('index.php?controller=admin&action=createUser');
        }
        
        // 2. Validação de Confirmação de Senha (Garantir que as senhas coincidam)
        if ($password !== $confirmPassword) {
            $_SESSION['admin_message'] = "Erro: As senhas digitadas não coincidem.";
            AuthManager::redirectTo('index.php?controller=admin&action=createUser');
        }

        // Tenta criar o usuário (o UserModel já tem a lógica de hashing)
        if ($this->userModel->create($username, $password, $cpf, $role)) {
            $_SESSION['admin_message'] = "Usuário {$username} criado com sucesso.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        } else {
            // Se o create falhar, geralmente é por erro de PDO (unicidade de Username ou CPF)
            $_SESSION['admin_message'] = "Erro ao criar usuário. É provável que o Nome de Usuário ou o CPF já estejam em uso.";
            AuthManager::redirectTo('index.php?controller=admin&action=createUser');
        }
    }
    
    // Ação para exibir o formulário de edição de usuário
    public function editUser() {
        $userId = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($userId); // UserModel::findById()

        if (!$user) {
            $_SESSION['admin_message'] = "Erro: Usuário não encontrado.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }
        
        $title = "Editar Usuário: {$user['username']}";

        $message = $_SESSION['admin_message'] ?? null;
        unset($_SESSION['admin_message']);

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        // Reutilizamos a view de formulário 
        include dirname(dirname(__DIR__)) . '/app/view/admin/user_form.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Ação para processar a edição de usuário
    public function processEditUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            $_SESSION['admin_message'] = "Erro: Requisição inválida.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        }

        $id = (int)$_POST['id'];
        $username = trim($_POST['username'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? ''; // Opcional, só se for alterada
        
        // Validações básicas
        if (empty($username) || empty($cpf)) {
            $_SESSION['admin_message'] = "Erro: Nome de usuário e CPF são obrigatórios.";
            AuthManager::redirectTo('index.php?controller=admin&action=editUser&id=' . $id);
        }
        if (strlen($cpf) !== 11) {
             $_SESSION['admin_message'] = "Erro: O CPF deve conter 11 dígitos.";
            AuthManager::redirectTo('index.php?controller=admin&action=editUser&id=' . $id);
        }

        // Tenta atualizar o usuário (UserModel::update())
        if ($this->userModel->update($id, $username, $cpf, $role, $password)) {
            $_SESSION['admin_message'] = "Usuário {$username} atualizado com sucesso.";
            AuthManager::redirectTo('index.php?controller=admin&action=users');
        } else {
            // Se o update falhar, geralmente é por erro de PDO (unicidade)
            $_SESSION['admin_message'] = "Erro ao atualizar usuário. É provável que o Nome de Usuário ou o CPF já estejam em uso por outro usuário.";
            AuthManager::redirectTo('index.php?controller=admin&action=editUser&id=' . $id);
        }
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
}
