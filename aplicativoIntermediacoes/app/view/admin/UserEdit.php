<?php
// app/view/admin/UserEdit.php
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
        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=admin&action=updateUser" method="POST" class="form-container">
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <div class="form-group">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>" class="input-field">
        </div>

        <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required value="<?= htmlspecialchars($user['cpf'] ?? '') ?>" class="input-field">
        </div>

        <div class="form-group">
            <label for="role">Função:</label>
            <select id="role" name="role" class="input-field">
                <option value="user" <?= ($user['role'] ?? '') === 'user' ? 'selected' : '' ?>>Usuário</option>
                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Nova Senha (deixe em branco para manter):</label>
            <input type="password" id="password" name="password" class="input-field">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="index.php?controller=admin&action=users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</main>
