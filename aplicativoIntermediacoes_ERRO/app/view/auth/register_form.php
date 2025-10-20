<?php
// app/view/auth/register_form.php

// A $auth_error e $auth_success devem ser passadas pelo Controller via $_SESSION, se existirem.
$error = $_SESSION['auth_error'] ?? null;
$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_error'], $_SESSION['auth_success']); 
?>
<main>
    <div class="form-container">
        <h2>Criar Nova Conta</h2>

        <?php if ($error): ?>
            <div class="message error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success-message">
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

            <button type="submit" class="submit-btn">Cadastrar</button>
        </form>
        
        <p class="mt-4 text-center">
            Já tem conta? <a href="index.php?controller=auth&action=login" class="link-secondary">Fazer Login</a>
        </p>
    </div>
</main>

<style>
    /* Estilos básicos para o formulário de cadastro */
    .form-container {
        max-width: 450px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .form-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 25px;
        border-bottom: 2px solid #5cb85c;
        padding-bottom: 10px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }
    .form-group input[type="text"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box; /* Garante que padding não afete a largura total */
    }
    .submit-btn {
        width: 100%;
        padding: 12px;
        background-color: #5cb85c;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1.1em;
        transition: background-color 0.3s;
        margin-top: 10px;
    }
    .submit-btn:hover {
        background-color: #4cae4c;
    }
    .message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-weight: bold;
    }
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .link-secondary {
        color: #007bff;
        text-decoration: none;
    }
    .link-secondary:hover {
        text-decoration: underline;
    }
</style>

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
