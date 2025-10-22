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
}
