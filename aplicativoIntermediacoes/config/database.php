<?php
// config/database.php

// Carrega variáveis de ambiente do arquivo .env (se existir)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse linha no formato KEY=VALUE
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Define como variável de ambiente
        if (!array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// Carrega .env do diretório raiz
loadEnv(__DIR__ . '/../.env');

// Função helper para pegar variáveis de ambiente
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

// Configurações do banco de dados
return [
    'DB_HOST'     => env('DB_HOST', 'localhost'),
    'DB_NAME'     => env('DB_NAME', 'INTERMEDIACOES'),
    'DB_USER'     => env('DB_USER', 'INTERMEDIACOES_USER'),
    'DB_PASS'     => env('DB_PASS', ''),
    'DB_CHARSET'  => env('DB_CHARSET', 'utf8mb4'),
    'TABLE_NAME'  => env('TABLE_NAME', 'INTERMEDIACOES_TABLE'),
    'USER_TABLE'  => env('USER_TABLE', 'USUARIOS_TABLE')
];
