<!-- app/view/auth/ChangePasswordForm.php -->
<main class="container">
    <div class="auth-container">
        <h1>Alterar Senha</h1>
        
        <!-- Mensagens de erro/sucesso -->
        <?php if (isset($_SESSION['auth_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['auth_error']) ?>
            </div>
            <?php unset($_SESSION['auth_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['auth_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['auth_success']) ?>
            </div>
            <?php unset($_SESSION['auth_success']); ?>
        <?php endif; ?>

        <form action="index.php?controller=auth&action=processChangePassword" method="POST" class="form">
            <div class="form-group">
                <label for="current_password">Senha Atual *</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="form-group">
                <label for="new_password">Nova Senha *</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required 
                    minlength="6"
                    autocomplete="new-password"
                >
                <small class="text-muted">Mínimo de 6 caracteres</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirme a Nova Senha *</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    minlength="6"
                    autocomplete="new-password"
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                <a href="index.php?controller=dashboard&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<style>
.auth-container {
    max-width: 500px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.auth-container h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
}

.text-muted {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    gap: 10px;
}

.form-actions .btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>
