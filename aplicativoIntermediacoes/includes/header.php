<?php
// includes/header.php - Header Moderno com Tema Verde e Dourado

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o AuthManager para verificar o status do login
require_once dirname(__DIR__) . '/app/util/AuthManager.php';

$authManager = new AuthManager();
$isLoggedIn = $authManager->isLoggedIn();
$user = $authManager->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Intermediações Financeiras">
    <title>Sistema de Intermediações Financeiras</title>
    
    <!-- CSS Moderno -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="includes/responsive-table.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>💰 Intermediações Financeiras</h1>
            
            <nav class="nav">
                <?php if ($isLoggedIn): ?>
                    <!-- Links de Navegação Principal -->
                    <a href="index.php?controller=dashboard&action=index">📊 Painel</a>
                    <a href="index.php?controller=upload&action=index">📥 Importar</a>
                    <a href="index.php?controller=negociacao&action=painel">💰 Negociações</a>
                    <a href="index.php?controller=dados&action=visualizar_negociadas">✅ Negociadas</a>
                    <a href="index.php?controller=relatorio&action=auditoria">📋 Auditoria</a>
                    
                    <?php if ($user && $user['role'] === 'admin'): ?>
                        <a href="index.php?controller=admin&action=users">👥 Usuários</a>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if (!$isLoggedIn): ?>
                    <a href="index.php?controller=auth&action=login">🔐 Login</a>
                    <a href="index.php?controller=auth&action=register">📝 Cadastro</a>
                <?php endif; ?>
            </nav>

            <!-- Informações do Usuário -->
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <span>👤 <?= htmlspecialchars($user['username'] ?? 'Usuário') ?></span>
                    <a href="index.php?controller=auth&action=logout" class="logout-btn">🚪 SAIR</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main>
