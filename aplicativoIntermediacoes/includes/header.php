<?php
// includes/header.php

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o AuthManager para verificar o status do login
require_once dirname(__DIR__) . '/app/util/AuthManager.php';

$authManager = new AuthManager();
$isLoggedIn = $authManager->isLoggedIn();
$currentUser = $authManager->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Intermediações Financeiras">
    <title>Sistema de Intermediações Financeiras</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="includes/responsive-table.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?controller=dashboard&action=index" class="logo"> 
            <h1>Gerenciamento Financeiro</h1>
            </a>
            
            <nav class="nav">
                <?php if ($isLoggedIn): ?>
                    <!-- Links de Navegação Principal -->
                    
                    <a href="index.php?controller=dashboard&action=index" 
                    style="border-bottom: 2px solid white;
                    padding-bottom: 2px;
                    text-decoration: none;
                    border-radius: 0;
                    color: white;">
                    Painel </a>

                    <a href="index.php?controller=upload&action=index" 
                    style="border-bottom: 2px solid goldenrod;
                    padding-bottom: 2px;
                    text-decoration: none;
                    border-radius: 0;
                    color: gold;">
                    Importar</a>

                    <a href="index.php?controller=negociacao&action=painel"
                    style="border-bottom: 2px solid cyan;
                    padding-bottom: 2px;
                    text-decoration: none;
                    border-radius: 0;
                    color: cyan;">
                    A Realizar</a>
                    
                    <a href="index.php?controller=dados&action=visualizar_negociadas"
                    style="border-bottom: 2px solid white;
                    padding-bottom: 2px;
                    text-decoration: none;
                    border-radius: 0;
                    color: white;">
                    Efetivadas</a>
                    
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <a href="index.php?controller=relatorio&action=auditoriaGeral"
                        style="border-bottom: 2px solid red;
                        padding-bottom: 2px;
                        text-decoration: none;
                        border-radius: 0;
                        color: red;">
                        Auditoria</a>
                        
                        <a href="index.php?controller=admin&action=users" 
                        style="border-bottom: 2px solid white;
                        padding-bottom: 2px;
                        text-decoration: none;
                        border-radius: 0;
                        color: white;">
                        Usuários</a>
                    <?php endif; ?>

                <?php endif; ?>
                <!-- Verificar se é necessário incluir algum menu no cabeçalho, sem login do usuário-->
                <?php if (!$isLoggedIn): ?>
                 <!--   <a href="index.php?controller=auth&action=login"> Login</a> -->
                <?php endif; ?>
            </nav>

            <!-- Informações do Usuário -->
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <span> <?= htmlspecialchars($currentUser['username'] ?? 'Usuário') ?></span>
                    <a href="index.php?controller=auth&action=logout" class="logout-btn"> SAIR</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
