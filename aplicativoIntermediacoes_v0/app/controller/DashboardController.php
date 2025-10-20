<?php
// app/controller/DashboardController.php

require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';

class DashboardController {
    private $authManager;

    public function __construct() {
        $this->authManager = new AuthManager();
    }

    public function index() {
        // Verifica se o usuário está logado
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }

        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $is_admin = $this->authManager->isAdmin();

        // Carrega o header/footer
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/dashboard/index.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }
}
