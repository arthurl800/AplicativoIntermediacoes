<?php
// app/view/admin/UserAdd.php

// Formulário simples para administrador criar um novo usuário
$error = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
?>
<main>
    <h2>Adicionar Novo Usuário</h2>

    <?php if ($error): ?>
        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=admin&action=createUser" method="POST" class="form-container">
        <div class="form-group">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" name="username" required class="input-field">
        </div>
        <div class="form-group">
            <label for="cpf">CPF (somente dígitos):</label>
            <input type="text" id="cpf" name="cpf" required maxlength="11" class="input-field">
        </div>
        <div class="form-group">
            <label for="role">Função:</label>
            <select id="role" name="role" class="input-field">
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required class="input-field">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar Senha:</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="input-field">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Criar Usuário</button>
            <a href="index.php?controller=admin&action=users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</main>
