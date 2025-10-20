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
$user = $authManager->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Intermediações Financeiras</title>
    <style>
        /* CORREÇÃO DO LAYOUT PARA RODAPÉ FIXO */
        html, body { 
            height: 100%; 
            margin: 0; 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f9;
        }
        body {
            display: flex;
            flex-direction: column; /* Organiza os filhos (header, main, footer) em coluna */
        }
        /* FIM CORREÇÃO RODAPÉ */

        .header { 
            background-color: #333; 
            color: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
            flex-shrink: 0; /* Impede que o header encolha */
        }
        .header h1 { 
            margin: 0; 
            font-size: 1.5em; 
            color: #5cb85c; /* Cor de destaque */
        }
        /* Garante que a navegação e o bloco de usuário fiquem em linha */
        .nav {
            display: flex;
            align-items: center;
            gap: 15px; /* Espaçamento entre os itens principais de navegação */
        }
        .nav a { 
            color: white; 
            text-decoration: none; 
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav a:hover { 
            background-color: #555; 
        }
        main { 
            padding: 20px; 
            max-width: 1200px; 
            margin: 20px auto; 
            background-color: white; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            flex-grow: 1; /* Permite que o conteúdo principal ocupe todo o espaço disponível */
            width: 90%; /* Ajuste de largura para mobile */
        }
        /* Garante que Olá, Usuário e SAIR fiquem em linha */
        .user-info {
            display: flex;
            align-items: center;
            margin-left: 25px; /* Espaço entre os links de navegação e as informações do usuário */
        }
        .user-info span {
            margin-right: 15px;
            color: #ccc;
            white-space: nowrap; /* Evita quebra de linha no nome do usuário */
        }
        .logout-btn {
            background-color: #d9534f; /* Vermelho para SAIR */
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #c9302c;
        }

        /* Estilos da tabela (para os próximos passos) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .data-table th {
            background-color: #5cb85c;
            color: white;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-delete { background-color: #d9534f; color: white; }
        .btn-delete:hover { background-color: #c9302c; }
        .btn-edit { background-color: #f0ad4e; color: white; }
        .btn-edit:hover { background-color: #ec971f; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Sistema de Intermediações</h1>
        
        <nav class="nav">
            <?php if ($isLoggedIn): ?>
                
                <!-- Links visíveis apenas quando logado -->
                <a href="index.php?controller=dashboard&action=index">Dashboard</a>
                <a href="index.php?controller=data&action=index">Dados Importados</a>
                
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <a href="index.php?controller=admin&action=users" style="background-color: #007bff;">Gerenciar Usuários</a>
                <?php endif; ?>

                <!-- Informações do usuário e botão SAIR -->
                <div class="user-info">
                    <span>Olá, <?= htmlspecialchars($user['username'] ?? 'Usuário') ?>!</span>
                    <a href="index.php?controller=auth&action=logout" class="logout-btn">SAIR</a>
                </div>

            <?php endif; ?>

            <?php if (!$isLoggedIn): ?>
                 <!-- Links visíveis apenas quando deslogado (opcional) -->
                <a href="index.php?controller=auth&action=login">Login</a>
                <a href="index.php?controller=auth&action=register">Cadastro</a>
            <?php endif; ?>

        </nav>
    </header>
    <!-- O conteúdo de cada página será inserido aqui pelo Controller -->
