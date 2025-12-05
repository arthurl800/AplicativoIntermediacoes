<?php
// app/view/admin/user_add.php
// Formulário simples para administrador criar um novo usuário
$error = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
?>
<main>
    <h2>Adicionar Novo Usuário</h2>

    <?php if ($error): ?>
        <div style="padding:10px;margin-bottom:12px;background:#f8d7da;color:#721c24;border-radius:6px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=admin&action=createUser" method="POST">
        <div>
            <label for="username">Nome de Usuário</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="cpf">CPF (somente dígitos)</label>
            <input type="text" id="cpf" name="cpf" required maxlength="11">
        </div>
        <div>
            <label for="role">Função</label>
            <select id="role" name="role">
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div>
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="confirm_password">Confirmar Senha</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div style="margin-top:12px;">
            <button type="submit" style="background:#007bff;color:#fff;padding:8px 12px;border-radius:6px;border:0;">Criar Usuário</button>
            <a href="index.php?controller=admin&action=users" style="margin-left:8px;">Cancelar</a>
        </div>
    </form>
</main>
