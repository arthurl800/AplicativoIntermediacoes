<?php
// app/view/admin/user_edit.php
// Exibe formulário de edição de usuário (somente administradores tem acesso via AdminController::editUser)

// $user está disponível do controller
if (!isset($user)) {
    echo '<p>Usuário não encontrado.</p>';
    return;
}

$error = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
?>
<main>
    <h2>Editar Usuário</h2>

    <?php if ($error): ?>
        <div style="padding:10px;margin-bottom:15px;background:#f8d7da;color:#721c24;border-radius:4px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=admin&action=updateUser" method="POST">
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <div>
            <label for="username">Nome de Usuário</label>
            <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
        </div>

        <div>
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" required value="<?= htmlspecialchars($user['cpf'] ?? '') ?>">
        </div>

        <div>
            <label for="role">Função</label>
            <select id="role" name="role">
                <option value="user" <?= ($user['role'] ?? '') === 'user' ? 'selected' : '' ?>>Usuário</option>
                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <div>
            <label for="password">Nova Senha (deixe em branco para manter)</label>
            <input type="password" id="password" name="password">
        </div>

        <div style="margin-top:12px;">
            <button type="submit" style="background:#28a745;color:#fff;padding:8px 12px;border-radius:6px;border:0;">Salvar</button>
            <a href="index.php?controller=admin&action=users" style="margin-left:8px;">Cancelar</a>
        </div>
    </form>
</main>
