<!-- app/view/auth/ForgotPassword.php -->
<?php
// Copiado de forgot_password.php para PascalCase
?>
<main style="max-width: 400px; margin-top: 50px;">
    <h2>Esqueci a Senha</h2>
    <p>Informe seu CPF abaixo para receber as instruções de recuperação de acesso.</p>

    <?php if (isset($message) && $message): ?>
        <div style="color: #333; border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; background-color: #f0f0f0; border-radius: 4px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=auth&action=resetPassword" method="POST">
        
        <div style="margin-bottom: 20px;">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required minlength="11" maxlength="14"
                   placeholder="Apenas números ou formato completo"
                   style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <button type="submit" 
                style="background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            Recuperar Senha
        </button>
    </form>
    
    <p style="margin-top: 20px; font-size: 0.9em; text-align: center;">
        <a href="index.php?controller=auth&action=login" style="color: #007bff; text-decoration: none;">Voltar para o Login</a>
    </p>
</main>
