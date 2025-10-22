<!-- app/view/auth/login_form.php -->
<main style="max-width: 400px; margin-top: 50px;">
    <h2>Acesso ao Sistema</h2>

    <?php 
    // Captura e limpa mensagens de sucesso/erro da sessão (definidas pelo Controller)
    $success = $_SESSION['auth_success'] ?? null;
    $error = $_SESSION['auth_error'] ?? null;
    unset($_SESSION['auth_success'], $_SESSION['auth_error']);
    ?>

    <?php if (isset($success) && $success): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; background-color: #e6ffe6; border-radius: 4px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; background-color: #ffe6e6; border-radius: 4px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=auth&action=processLogin" method="POST">
        
        <div style="margin-bottom: 15px;">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" name="username" required 
                   style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required 
                   style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <button type="submit" 
                style="background-color: #5cb85c; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">
            Entrar
        </button>
    </form>
    
    <!-- NOVIDADE: Links de Acesso Rápido -->
    <div style="margin-top: 20px; font-size: 0.9em; display: flex; justify-content: space-between;">
        <a href="index.php?controller=auth&action=forgotPassword" style="color: #007bff; text-decoration: none;">
            Esqueci a senha
        </a>
        <a href="index.php?controller=auth&action=register" style="color: #5cb85c; text-decoration: none; font-weight: bold;">
            Criar Conta
        </a>
    </div>
</main>
