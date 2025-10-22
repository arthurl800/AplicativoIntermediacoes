<?php
// config/database.php

/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'projetoIntermediacoes');
define('DB_USER', 'userIntermediacoes');
define('DB_PASS', '%intermediacoes999$#');
define('DB_CHARSET', 'utf8');
*/


// Este arquivo deve retornar um array de configuração
return [
    'DB_HOST'     => 'localhost',
    'DB_NAME'     => 'INTERMEDIACOES',
    'DB_USER'     => 'INTERMEDIACOES_USER',
    'DB_PASS'     => '%intermediacoes999$#',
    'DB_CHARSET'  => 'utf8mb4',
    'TABLE_NAME'  => 'INTERMEDIACOES_TABLE',   // Nome da tabela para o upload de dados
    'USER_TABLE'  => 'USUARIOS_TABLE'          // Nome da tabela para usuários
];
