<!-- app/view/auth/ForgotPassword.php -->
<?php
// Copiado de forgot_password.php para PascalCase
?>
<main>
    <div class="form-container">
    <h2>Esqueci a Senha</h2>
    <p>Informe seu CPF abaixo para receber as instruções de recuperação de acesso.</p>

    <?php if (isset($message) && $message): ?>
        <div class="message info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=auth&action=resetPassword" method="POST" class="form-group">
        
        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required minlength="11" maxlength="14"
               placeholder="Apenas números ou formato completo" class="input-field">
        
        <button type="submit" class="btn btn-warning">
            Recuperar Senha
        </button>
    </form>
    
    <p class="text-center mt-4">
        <a href="index.php?controller=auth&action=login" class="btn-link">Voltar para o Login</a>
    </p>
    </div>
</main>
