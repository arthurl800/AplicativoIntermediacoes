<?php
// app/view/auth/RegisterForm.php

// A $auth_error e $auth_success devem ser passadas pelo Controller via $_SESSION, se existirem.
$error = $_SESSION['auth_error'] ?? null;
$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_error'], $_SESSION['auth_success']); 
?>
<main>
    <div class="form-container">
        <h2>Criar Nova Conta</h2>

        <?php if ($error): ?>
            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?controller=auth&action=processRegister" method="POST" id="registerForm">
            <div class="form-group">
                <label for="username">Nome de Usuário:</label>
                <input type="text" id="username" name="username" required 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="cpf">CPF:</label>
                <!-- O input recebe a máscara -->
                <input type="text" id="cpf" name="cpf_masked" 
                       placeholder="000.000.000-00" maxlength="14" required>
                <!-- Campo hidden para enviar o CPF sem máscara (apenas dígitos) ao backend -->
                <input type="hidden" id="cpf_clean" name="cpf" value="">
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cpfInputMasked = document.getElementById('cpf');
        const cpfInputClean = document.getElementById('cpf_clean');
        const registerForm = document.getElementById('registerForm');

        function maskCPF(value) {
            // Remove tudo que não for dígito
            value = value.replace(/\D/g, "");
            
            // Aplica a máscara: 000.000.000-00
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            
            return value;
        }

        // Aplica a máscara no input visível
        cpfInputMasked.addEventListener('input', function(e) {
            const maskedValue = maskCPF(e.target.value);
            e.target.value = maskedValue;
            
            // Atualiza o campo hidden com o valor limpo (apenas dígitos)
            cpfInputClean.value = e.target.value.replace(/\D/g, "");
        });

        // Garante que o campo hidden seja preenchido no submit
        registerForm.addEventListener('submit', function() {
            cpfInputClean.value = cpfInputMasked.value.replace(/\D/g, "");
            
            // Opcional: Adicionar validação de tamanho (11 dígitos) antes de enviar
            if (cpfInputClean.value.length !== 11) {
                alert('O CPF deve ter 11 dígitos.');
                return false;
            }
            return true;
        });
    });
</script>
