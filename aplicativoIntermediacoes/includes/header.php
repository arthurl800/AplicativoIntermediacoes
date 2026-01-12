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
    <meta name="description" content="Sistema de Intermediações Financeiras - Experimento Tecnologias">
    <title>Sistema de Intermediações - Experimento Tecnologias</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="includes/responsive-table.css">
    
    <style>
        .modern-header {
            background: var(--bg-primary);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: var(--space-md) var(--space-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            text-decoration: none;
        }
        
        .header-logo img {
            height: 40px;
        }
        
        .header-logo h1 {
            font-size: 1.25rem;
            margin: 0;
            color: var(--text-primary);
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-nav {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .nav-link {
            padding: var(--space-sm) var(--space-md);
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            border-radius: var(--radius-md);
            transition: all var(--transition-base);
            white-space: nowrap;
        }
        
        .nav-link:hover {
            background: var(--gray-100);
            color: #667eea;
        }
        
        .nav-link.active {
            background: var(--primary-gradient);
            color: white;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-left: var(--space-lg);
            padding-left: var(--space-lg);
            border-left: 2px solid var(--gray-200);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .header-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg-primary);
                flex-direction: column;
                padding: var(--space-lg);
                box-shadow: var(--shadow-lg);
            }
            
            .header-nav.active {
                display: flex;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .user-menu {
                margin-left: 0;
                padding-left: 0;
                border-left: none;
                border-top: 2px solid var(--gray-200);
                padding-top: var(--space-md);
                width: 100%;
            }
            
            .nav-link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="modern-header">
        <div class="header-container">
            <a href="landing.php" class="header-logo">
                <img src="assets/images/logo-icon.svg" alt="Experimento Tecnologias">
                <h1>Experimento</h1>
            </a>
            
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                ☰
            </button>
            
            <?php if ($isLoggedIn): ?>
                <nav class="header-nav" id="headerNav">
                    <a href="index.php?controller=dashboard&action=index" class="nav-link">Painel</a>
                    <a href="index.php?controller=upload&action=index" class="nav-link">Importar Dados</a>
                    <a href="index.php?controller=data&action=viewData" class="nav-link">Visualizar Dados</a>
                    <a href="index.php?controller=negociacao&action=index" class="nav-link">Negociações</a>
                    <a href="index.php?controller=relatorio&action=auditoria" class="nav-link">Relatórios</a>
                    
                    <?php if ($authManager->hasRole('admin')): ?>
                        <a href="index.php?controller=admin&action=userList" class="nav-link">Administração</a>
                    <?php endif; ?>
                    
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($currentUser['username'] ?? 'Usuário') ?></span>
                            <span class="user-role"><?= htmlspecialchars($currentUser['role'] ?? 'user') ?></span>
                        </div>
                        <a href="index.php?controller=auth&action=logout" class="btn btn-sm btn-outline">Sair</a>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="container fade-in">
    
    <script>
        function toggleMobileMenu() {
            const nav = document.getElementById('headerNav');
            nav.classList.toggle('active');
        }
        
        // Marca o link ativo
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.href;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (currentPage.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
