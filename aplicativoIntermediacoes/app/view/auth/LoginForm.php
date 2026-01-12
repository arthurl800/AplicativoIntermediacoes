<?php
// app/view/auth/LoginForm.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Experimento Tecnologias</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #000000 100%);
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: var(--space-xl);
        }
        
        .login-card {
            background: white;
            padding: var(--space-2xl);
            border-radius: var(--radius-2xl);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: slideUp 0.6s ease-out;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }
        
        .login-logo {
            width: 200px;
            margin-bottom: var(--space-lg);
        }
        
        .login-title {
            font-size: 1.75rem;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .login-form {
            background: transparent;
            padding: 0;
            box-shadow: none;
        }
        
        .login-form .form-group {
            margin-bottom: var(--space-lg);
        }
        
        .login-form label {
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .login-form input {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all var(--transition-base);
            background: white;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: #1b5e20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
        }
        
        .login-form .btn {
            margin-top: var(--space-md);
            font-size: 1.125rem;
            padding: var(--space-md) var(--space-xl);
        }
        
        .back-link {
            text-align: center;
            margin-top: var(--space-xl);
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            transition: all var(--transition-base);
            display: inline-block;
        }
        
        .back-link a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.svg" alt="Experimento Tecnologias" class="login-logo">
                <h2 class="login-title">Acesso ao Sistema</h2>
                <p class="login-subtitle">Entre com suas credenciais</p>
            </div>

            <?php 
            $success = $_SESSION['auth_success'] ?? null;
            $error = $_SESSION['auth_error'] ?? null;
            unset($_SESSION['auth_success'], $_SESSION['auth_error']);
            ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?controller=auth&action=processLogin" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Nome de Usuário</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    Entrar no Sistema
                </button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="landing.php">Voltar para página inicial</a>
        </div>
    </div>
</body>
</html>
