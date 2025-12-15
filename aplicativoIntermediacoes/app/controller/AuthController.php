<?php
// app/controller/AuthController.php

namespace App\Controller;

use App\Model\UserModel;
use App\Util\AuthManager;

// TODO: Implementar um mecanismo de renderização de views para evitar includes diretos.

class AuthController {
    private $userModel;
    private $authManager;

    public function __construct() {
        // Assegura que o UserModel e AuthManager são instanciados
        $this->userModel = new UserModel();
        $this->authManager = new AuthManager();
    }

    // Exibe o formulário de login
    public function login() {
        // Se já estiver logado, redireciona para o dashboard
        if ($this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('/?controller=dashboard&action=index');
        }

        // As mensagens de erro e sucesso são capturadas na view, 
        // a inclusão do header e footer é necessária aqui.
        
        $base_dir = dirname(dirname(__DIR__)); // TODO: Remover após implementar renderizador de view
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/auth/LoginForm.php'; 
        include $base_dir . '/includes/footer.php';
    }

    // Processa a submissão do formulário de login
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('/?controller=auth&action=login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByUsername($username);

        // Verifica se o usuário existe e se a senha está correta
        if ($user && password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $this->authManager->login($user);
            AuthManager::redirectTo('/?controller=dashboard&action=index');
        } else {
            // Falha no login
            $_SESSION['auth_error'] = "Usuário ou senha inválidos.";
            AuthManager::redirectTo('/?controller=auth&action=login');
        }
    }

    // Exibe o formulário de cadastro
    public function register() {
        // Registro de novos usuários. Este só pode ser realizado por administradores
        if (!$this->authManager->isAdmin()) {
            // Se o usuário não for admin (ou não estiver logado), negar acesso
            $_SESSION['auth_error'] = "Acesso negado. Apenas administradores podem cadastrar novos usuários.";
            AuthManager::redirectTo('/?controller=auth&action=login');
            return;
        }
        
        $base_dir = dirname(dirname(__DIR__)); // TODO: Remover após implementar renderizador de view
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/auth/RegisterForm.php'; 
        include $base_dir . '/includes/footer.php';
    }

    // Processa a submissão do formulário de cadastro
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('/?controller=auth&action=register');
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $cpf = trim($_POST['cpf'] ?? ''); // CPF sem formatação.
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Validação básica pois o front-end deve ter a validação primária
        if (empty($username) || empty($cpf) || empty($password) || empty($confirmPassword)) {
            $_SESSION['auth_error'] = "Todos os campos são obrigatórios.";
            AuthManager::redirectTo('/?controller=auth&action=register');
            return;
        }
        if ($password !== $confirmPassword) {
            $_SESSION['auth_error'] = "As senhas não coincidem.";
            AuthManager::redirectTo('/?controller=auth&action=register');
            return;
        }
        if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
             $_SESSION['auth_error'] = "O CPF deve conter 11 dígitos numéricos válidos.";
            AuthManager::redirectTo('/?controller=auth&action=register');
            return;
        }

        // 2. Tenta criar o usuário
        if ($this->userModel->create($username, $password, $cpf)) {
            // Sucesso no cadastro
            $_SESSION['auth_success'] = "Usuário {$username} cadastrado com sucesso!";
            AuthManager::redirectTo('/?controller=auth&action=login');
        } else {
            // Falha no cadastro (Pode ser duplicidade, erro de SQL, etc.)
            
            // Tentativa de dar um feedback mais preciso ao usuário
            $userExists = $this->userModel->findByUsername($username);
            
            if ($userExists) {
                 $_SESSION['auth_error'] = "O nome de usuário '{$username}' já está em uso.";
            } else {
                // Se não for duplicidade, o erro é mais crítico (conexão, sintaxe SQL, permissão).
                // O erro real estará no log do servidor (via error_log no UserModel).
                $_SESSION['auth_error'] = "Erro interno ao cadastrar o usuário. Por favor, tente novamente.";
            }
            
            // Redireciona de volta para o formulário de cadastro com a mensagem de erro
            AuthManager::redirectTo('/?controller=auth&action=register');
        }
    }

    // Ação de logout
    public function logout() {
        $this->authManager->logout();
        AuthManager::redirectTo('/?controller=auth&action=login');
    }
}