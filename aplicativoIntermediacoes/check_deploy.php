#!/usr/bin/env php
<?php
/**
 * Script de verificação pré-deploy
 * Verifica se o sistema está pronto para publicação
 */

echo "\n🔍 VERIFICAÇÃO PRÉ-DEPLOY\n";
echo "========================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar arquivo .env
echo "1. Verificando arquivo .env... ";
if (file_exists(__DIR__ . '/.env')) {
    $success[] = "✅ Arquivo .env encontrado";
    
    // Verificar se não está usando valores padrão perigosos
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'sua_senha_aqui') !== false) {
        $errors[] = "❌ .env ainda contém 'sua_senha_aqui' - configure suas credenciais!";
    }
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        $warnings[] = "⚠️  APP_DEBUG está ativado - desative em produção!";
    }
    if (strpos($envContent, 'APP_ENV=development') !== false) {
        $warnings[] = "⚠️  APP_ENV=development - mude para 'production'!";
    }
} else {
    $errors[] = "❌ Arquivo .env não encontrado - copie .env.example para .env";
}

// 2. Verificar .htaccess
echo "\n2. Verificando .htaccess... ";
if (file_exists(__DIR__ . '/.htaccess')) {
    $success[] = "✅ Arquivo .htaccess encontrado";
    
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($htaccess, 'RewriteBase /aplicativoIntermediacoes/') !== false) {
        $warnings[] = "⚠️  .htaccess configurado para desenvolvimento - ajuste RewriteBase para produção (/)";
    }
} else {
    $warnings[] = "⚠️  Arquivo .htaccess não encontrado";
}

// 3. Verificar config/database.php
echo "\n3. Verificando config/database.php... ";
if (file_exists(__DIR__ . '/config/database.php')) {
    $success[] = "✅ config/database.php encontrado";
    
    // Verificar se usa .env
    $dbConfig = file_get_contents(__DIR__ . '/config/database.php');
    if (strpos($dbConfig, 'loadEnv') !== false || strpos($dbConfig, 'getenv') !== false) {
        $success[] = "✅ database.php usa variáveis de ambiente";
    } else {
        $warnings[] = "⚠️  database.php pode ter credenciais hardcoded";
    }
} else {
    $errors[] = "❌ config/database.php não encontrado";
}

// 4. Verificar vendor/autoload.php
echo "\n4. Verificando dependências Composer... ";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $success[] = "✅ Dependências Composer instaladas";
} else {
    $errors[] = "❌ Execute 'composer install' antes do deploy";
}

// 5. Verificar setup_database.sql
echo "\n5. Verificando script de banco de dados... ";
if (file_exists(__DIR__ . '/setup_database.sql')) {
    $success[] = "✅ setup_database.sql encontrado";
} else {
    $warnings[] = "⚠️  setup_database.sql não encontrado";
}

// 6. Verificar estrutura de pastas
echo "\n6. Verificando estrutura de pastas... ";
$requiredDirs = ['app', 'app/controller', 'app/model', 'app/view', 'config', 'assets', 'includes'];
$missingDirs = [];
foreach ($requiredDirs as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir)) {
        $missingDirs[] = $dir;
    }
}
if (empty($missingDirs)) {
    $success[] = "✅ Todas as pastas necessárias existem";
} else {
    $errors[] = "❌ Pastas faltando: " . implode(', ', $missingDirs);
}

// 7. Verificar permissões
echo "\n7. Verificando permissões (logs)... ";
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
if (is_writable(__DIR__ . '/logs')) {
    $success[] = "✅ Pasta logs tem permissão de escrita";
} else {
    $warnings[] = "⚠️  Pasta logs sem permissão de escrita - ajuste para 755";
}

// 8. Verificar arquivos sensíveis no .gitignore
echo "\n8. Verificando .gitignore... ";
if (file_exists(__DIR__ . '/.gitignore')) {
    $gitignore = file_get_contents(__DIR__ . '/.gitignore');
    if (strpos($gitignore, '.env') !== false) {
        $success[] = "✅ .env está no .gitignore";
    } else {
        $errors[] = "❌ Adicione .env ao .gitignore!";
    }
} else {
    $warnings[] = "⚠️  .gitignore não encontrado";
}

// 9. Verificar index.php
echo "\n9. Verificando index.php... ";
if (file_exists(__DIR__ . '/index.php')) {
    $success[] = "✅ index.php encontrado";
} else {
    $errors[] = "❌ index.php não encontrado";
}

// Relatório Final
echo "\n\n";
echo "========================================\n";
echo "📊 RELATÓRIO FINAL\n";
echo "========================================\n\n";

if (!empty($success)) {
    echo "✅ SUCESSOS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVISOS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERROS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

echo "========================================\n";

if (empty($errors)) {
    if (empty($warnings)) {
        echo "🎉 SISTEMA PRONTO PARA DEPLOY!\n\n";
        echo "Próximos passos:\n";
        echo "1. Leia DEPLOY_GUIDE.md\n";
        echo "2. Escolha sua hospedagem (InfinityFree recomendado)\n";
        echo "3. Faça upload dos arquivos\n";
        echo "4. Configure .env com credenciais da hospedagem\n";
        echo "5. Importe setup_database.sql no phpMyAdmin\n";
        echo "6. Acesse seu site e faça login!\n\n";
        exit(0);
    } else {
        echo "⚠️  SISTEMA PODE SER PUBLICADO COM AJUSTES\n";
        echo "Corrija os avisos acima antes do deploy em produção.\n\n";
        exit(0);
    }
} else {
    echo "❌ CORRIJA OS ERROS ANTES DO DEPLOY!\n\n";
    exit(1);
}
