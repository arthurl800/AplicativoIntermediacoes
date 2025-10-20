<?php

include_once '../model/Usuario.php';

// Dados do novo usuário
$nome = 'Admin';
$email = 'admin@exemplo.com';
$senha = '12345'; // A senha que será criptografada

// Cria uma nova instância da classe Usuario
$novoUsuario = new Usuario(null, $nome, $email, $senha);

// Tenta cadastrar o novo usuário
if ($novoUsuario->create()) {
    print "Usuário cadastrado com sucesso!\n";
} else {
    print "Erro ao cadastrar o usuário.\n";
}
?>