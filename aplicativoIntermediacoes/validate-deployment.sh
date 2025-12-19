#!/bin/bash
# Pre-Deployment Validation Script
# Execute este script antes de fazer deploy em produção

set -e

echo "=== Validação Pré-Deployment ==="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

errors=0
warnings=0

# Função para log
log_error() {
    echo -e "${RED}[ERRO]${NC} $1"
    ((errors++))
}

log_warn() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
    ((warnings++))
}

log_ok() {
    echo -e "${GREEN}[OK]${NC} $1"
}

# 1. Verificar arquivos obrigatórios
echo "1. Verificando arquivos obrigatórios..."
required_files=(".env" "docker-compose.prod.yml" "Dockerfile" "docker/php-fpm/Dockerfile" "docker/nginx/default.conf" "index.php" "config/Config.php")

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        log_ok "Encontrado: $file"
    else
        log_error "Faltando: $file"
    fi
done
echo ""

# 2. Verificar variáveis de ambiente
echo "2. Verificando variáveis de ambiente..."
if [ -f ".env" ]; then
    if grep -q "DB_PASSWORD=" .env && ! grep -q "DB_PASSWORD=your_secure_password_here" .env; then
        log_ok ".env configurado com senha do banco"
    else
        log_error ".env não foi personalizado (verifique DB_PASSWORD)"
    fi
    
    if grep -q "APP_ENV=production" .env; then
        log_ok "APP_ENV definido como production"
    else
        log_warn "APP_ENV não está definido como production"
    fi
else
    log_error ".env não encontrado"
fi
echo ""

# 3. Verificar Docker
echo "3. Verificando Docker e Docker Compose..."
if command -v docker &> /dev/null; then
    log_ok "Docker instalado: $(docker --version)"
else
    log_error "Docker não instalado"
fi

if command -v docker-compose &> /dev/null; then
    log_ok "Docker Compose instalado: $(docker-compose --version)"
else
    log_error "Docker Compose não instalado"
fi
echo ""

# 4. Verificar sintaxe dos Dockerfiles
echo "4. Verificando sintaxe dos Dockerfiles..."
if docker build --dry-run -f Dockerfile . > /dev/null 2>&1; then
    log_ok "Dockerfile válido"
else
    log_error "Dockerfile contém erros de sintaxe"
fi
echo ""

# 5. Verificar compose.yml
echo "5. Validando docker-compose.prod.yml..."
if docker-compose -f docker-compose.prod.yml config > /dev/null 2>&1; then
    log_ok "docker-compose.prod.yml válido"
else
    log_error "docker-compose.prod.yml contém erros"
fi
echo ""

# 6. Verificar permissões de arquivo
echo "6. Verificando permissões..."
if [ -w "." ]; then
    log_ok "Diretório tem permissão de escrita"
else
    log_warn "Diretório não tem permissão de escrita (pode ser esperado em produção)"
fi
echo ""

# 7. Verificar dependencies
echo "7. Verificando dependências PHP..."
if [ -f "composer.json" ]; then
    log_ok "composer.json encontrado"
    if [ ! -d "vendor" ]; then
        log_warn "vendor/ não encontrado - será instalado durante build"
    else
        log_ok "vendor/ presente"
    fi
else
    log_error "composer.json não encontrado"
fi
echo ""

# 8. Verificar banco de dados
echo "8. Verificando arquivos de banco de dados..."
if [ -f "docker/mysql/init/01-init.sql" ]; then
    log_ok "Script SQL de inicialização presente"
else
    log_warn "Script SQL de inicialização não encontrado"
fi
echo ""

# 9. Verificar logs e tmp
echo "9. Verificando diretórios..."
mkdir -p logs tmp certs
log_ok "Diretórios necessários criados/verificados"
echo ""

# 10. Verificar configurações de segurança
echo "10. Verificando configurações de segurança..."
if grep -q "HTTPS" DEPLOYMENT.md; then
    log_ok "Documentação menciona HTTPS"
else
    log_warn "Verifique se HTTPS está configurado"
fi

if grep -q "X-Frame-Options" docker/nginx/default.conf; then
    log_ok "Headers de segurança presentes no Nginx"
else
    log_error "Headers de segurança não encontrados no Nginx"
fi
echo ""

# Resumo
echo "=== RESUMO ==="
echo -e "${GREEN}OK:${NC} 0"
echo -e "${YELLOW}Avisos:${NC} $warnings"
echo -e "${RED}Erros:${NC} $errors"
echo ""

if [ $errors -eq 0 ]; then
    echo -e "${GREEN}✓ Todos os pré-requisitos foram atendidos!${NC}"
    echo "Próximo passo: docker-compose -f docker-compose.prod.yml up -d"
    exit 0
else
    echo -e "${RED}✗ Existem erros que precisam ser corrigidos antes do deploy.${NC}"
    exit 1
fi
