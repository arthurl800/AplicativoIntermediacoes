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
    'DB_NAME'     => 'projetoIntermediacoes',
    'DB_USER'     => 'userIntermediacoes',
    'DB_PASS'     => '%intermediacoes999$#',
    'DB_CHARSET'  => 'utf8',
    'TABLE_NAME'  => 'INTERMEDIACOES', // Nome da tabela para o upload de dados
    'USER_TABLE' => 'USERS',       // Nome da tabela para usuários
];