<?php
// app/view/auth/LoginForm.php
?>
<main>
    <div class="form-container">
    <h2>Acesso ao Sistema</h2> 

    <?php 
    // Captura e limpa mensagens de sucesso/erro da sessão (definidas pelo Controller)
    $success = $_SESSION['auth_success'] ?? null;
    $error = $_SESSION['auth_error'] ?? null;
    unset($_SESSION['auth_success'], $_SESSION['auth_error']);
    ?>

    <?php if ($success): ?>
        <div class="message success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=auth&action=processLogin" method="POST" class="form-group">
    <br>    
        <label for="username">Nome de Usuário:</label>
        <input type="text" id="username" name="username" required class="input-field">
        
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required class="input-field">
    </br>
    <br>
        <button type="submit" class="btn btn-primary">
            Entrar
        </button>
    </br>    
    </form>
    
    <!-- Link para a página de recuperação de senha
    <div class="form-actions-bottom">
        <a href="index.php?controller=auth&action=forgotPassword" class="btn-link">
            Esqueci a senha
        </a>
    </div>
     -->

    </div>
</main>
