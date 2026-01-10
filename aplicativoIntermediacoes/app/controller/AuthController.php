<?php
// app/controller/AuthController.php

// Define o caminho base do projeto (o diretório acima da pasta 'app')
$base_dir = dirname(dirname(__DIR__));

// Inclui dependências
require_once $base_dir . '/app/model/UserModel.php';
require_once $base_dir . '/app/util/AuthManager.php';
require_once $base_dir . '/app/util/AuditLogger.php';

class AuthController {
    private $userModel;
    private $authManager;
    private $auditLogger;

    public function __construct() {
        // Assegura que o UserModel e AuthManager são instanciados
        $this->userModel = new UserModel();
        $this->authManager = new AuthManager();
        $this->auditLogger = AuditLogger::getInstance();
    }

    // Exibe o formulário de login
    public function login() {
        // Se já estiver logado, redireciona para o dashboard
        if ($this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=dashboard&action=index');
        }

        // As mensagens de erro e sucesso são capturadas na view, 
        // a inclusão do header e footer é necessária aqui.
        
        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/auth/LoginForm.php'; 
        include $base_dir . '/includes/footer.php';
    }

    // Processa a submissão do formulário de login
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByUsername($username);

        // Verifica se o usuário existe e se a senha está correta
        if ($user && password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $this->authManager->login($user);
            
            // Registra login bem-sucedido
            $this->auditLogger->logLogin($user['id'], $user['username'], true);
            
            AuthManager::redirectTo('index.php?controller=dashboard&action=index');
        } else {
            // Registra tentativa de login falha
            if ($user) {
                $this->auditLogger->logLogin($user['id'], $user['username'], false);
            } else {
                $this->auditLogger->log('LOGIN_FALHA', 'AUTENTICACAO', "Tentativa de login com usuário inexistente: {$username}");
            }
            
            // Falha no login
            $_SESSION['auth_error'] = "Usuário ou senha inválidos.";
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }
    }

    // Exibe o formulário de cadastro
    public function register() {
        // Registro de novos usuários. Este só pode ser realizado por administradores
        if (!$this->authManager->isAdmin()) {
            // Se o usuário não for admin (ou não estiver logado), negar acesso
            $_SESSION['auth_error'] = "Apenas administradores podem cadastrar novos usuários.";
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            return;
        }
        
        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/auth/RegisterForm.php'; 
        include $base_dir . '/includes/footer.php';
    }

    // Processa a submissão do formulário de cadastro
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=auth&action=register');
        }
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $cpf = trim($_POST['cpf'] ?? ''); // CPF sem formatação.
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Validação básica pois o front-end deve ter a validação primária
        if (empty($username) || empty($email) || empty($cpf) || empty($password) || empty($confirmPassword)) {
            $_SESSION['auth_error'] = "Todos os campos são obrigatórios.";
            AuthManager::redirectTo('index.php?controller=auth&action=register');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = "E-mail inválido.";
            AuthManager::redirectTo('index.php?controller=auth&action=register');
            return;
        }
        if ($password !== $confirmPassword) {
            $_SESSION['auth_error'] = "As senhas não coincidem.";
            AuthManager::redirectTo('index.php?controller=auth&action=register');
            return;
        }
        if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
             $_SESSION['auth_error'] = "O CPF deve conter 11 dígitos numéricos válidos.";
            AuthManager::redirectTo('index.php?controller=auth&action=register');
            return;
        }

        // 2. Tenta criar o usuário
        if ($this->userModel->create($username, $password, $cpf, 'user', $email)) {
            // Registra criação de usuário
            $this->auditLogger->logCreate('USUARIOS', "Novo usuário criado: {$username}", [
                'username' => $username,
                'email' => $email,
                'cpf' => $cpf
            ]);
            
            // Sucesso no cadastro
            $_SESSION['auth_success'] = "Usuário {$username} cadastrado com sucesso! Faça login.";
            AuthManager::redirectTo('index.php?controller=auth&action=login');
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
            AuthManager::redirectTo('index.php?controller=auth&action=register');
        }
    }

    // Ação de logout
    public function logout() {
        $currentUser = $this->authManager->getCurrentUser();
        if ($currentUser) {
            $this->auditLogger->logLogout($currentUser['id'], $currentUser['username']);
        }
        
        $this->authManager->logout();
        AuthManager::redirectTo('index.php?controller=auth&action=login');
    }

    // Exibe formulário para alterar a própria senha
    public function changePassword() {
        // Apenas usuários logados podem acessar
        if (!$this->authManager->isLoggedIn()) {
            $_SESSION['auth_error'] = "Você precisa estar logado para alterar sua senha.";
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            return;
        }
        
        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        include $base_dir . '/app/view/auth/ChangePasswordForm.php'; 
        include $base_dir . '/includes/footer.php';
    }

    // Processa a alteração de senha
    public function processChangePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
            return;
        }

        // Apenas usuários logados podem alterar senha
        if (!$this->authManager->isLoggedIn()) {
            $_SESSION['auth_error'] = "Você precisa estar logado.";
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            return;
        }

        $currentUser = $this->authManager->getCurrentUser();
        $userId = $currentUser['id'];
        $username = $currentUser['username'];

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validações
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['auth_error'] = "Todos os campos são obrigatórios.";
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['auth_error'] = "A nova senha e a confirmação não coincidem.";
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
            return;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['auth_error'] = "A nova senha deve ter pelo menos 6 caracteres.";
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
            return;
        }

        // Verifica a senha atual
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['auth_error'] = "Senha atual incorreta.";
            $this->auditLogger->log('CHANGE_PASSWORD_FALHA', 'AUTENTICACAO', 
                "Tentativa falha de alteração de senha: senha atual incorreta", 
                null, null, $userId, $username);
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
            return;
        }

        // Atualiza a senha
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            $_SESSION['auth_success'] = "Senha alterada com sucesso!";
            $this->auditLogger->log('CHANGE_PASSWORD_SUCESSO', 'AUTENTICACAO', 
                "Usuário {$username} alterou sua senha", 
                null, null, $userId, $username);
            AuthManager::redirectTo('index.php?controller=dashboard&action=index');
        } else {
            $_SESSION['auth_error'] = "Erro ao alterar senha. Tente novamente.";
            AuthManager::redirectTo('index.php?controller=auth&action=changePassword');
        }
    }
}