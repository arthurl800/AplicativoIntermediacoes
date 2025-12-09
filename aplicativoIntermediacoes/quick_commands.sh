#!/bin/bash
# quick_commands.sh - Comandos úteis para trabalhar com o Dashboard

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║           COMANDOS RÁPIDOS - Dashboard de Negociações          ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Cores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}1. INICIAR SERVIDOR PHP:${NC}"
echo "   cd /var/www/html/aplicativoIntermediacoes && php -S localhost:8000"
echo ""

echo -e "${BLUE}2. VALIDAR SINTAXE PHP:${NC}"
echo "   php -l app/controller/RelatorioController.php"
echo "   php -l app/model/AuditoriaModel.php"
echo "   php -l app/view/relatorio/dashboard.php"
echo "   php -l app/view/relatorio/auditoria.php"
echo ""

echo -e "${BLUE}3. VALIDAR BANCO DE DADOS:${NC}"
echo "   mysql -u INTERMEDIACOES_USER -p'%intermediacoes999\$#' -h localhost INTERMEDIACOES -e \"SHOW TABLES LIKE 'VW_%';\""
echo ""

echo -e "${BLUE}4. TESTAR CONEXÃO MYSQL:${NC}"
echo "   mysql -u INTERMEDIACOES_USER -p'%intermediacoes999\$#' -h localhost INTERMEDIACOES -e \"SELECT 1;\""
echo ""

echo -e "${BLUE}5. EXECUTAR SETUP SQL:${NC}"
echo "   mysql -u INTERMEDIACOES_USER -p'%intermediacoes999\$#' -h localhost INTERMEDIACOES < database/setup_analytics.sql"
echo ""

echo -e "${BLUE}6. ACESSAR DASHBOARD:${NC}"
echo "   http://localhost:8000/index.php?controller=relatorio&action=dashboard"
echo ""

echo -e "${BLUE}7. ACESSAR AUDITORIA:${NC}"
echo "   http://localhost:8000/index.php?controller=relatorio&action=auditoria"
echo ""

echo -e "${BLUE}8. EXPORTAR RELATÓRIO:${NC}"
echo "   http://localhost:8000/index.php?controller=relatorio&action=exportarCSV"
echo ""

echo -e "${BLUE}9. EXPORTAR COM PERÍODO:${NC}"
echo "   http://localhost:8000/index.php?controller=relatorio&action=exportarCSV&data_inicio=2025-12-01&data_fim=2025-12-31"
echo ""

echo -e "${BLUE}10. TESTAR DADOS DO DASHBOARD:${NC}"
echo "    http://localhost:8000/test_dashboard.php"
echo ""

echo -e "${BLUE}11. CONTAR NEGOCIAÇÕES:${NC}"
echo "    mysql -u INTERMEDIACOES_USER -p'%intermediacoes999\$#' -h localhost INTERMEDIACOES -e \"SELECT COUNT(*) as total FROM NEGOCIACOES;\""
echo ""

echo -e "${BLUE}12. VER KPIs (VIEW):${NC}"
echo "    mysql -u INTERMEDIACOES_USER -p'%intermediacoes999\$#' -h localhost INTERMEDIACOES -e \"SELECT * FROM VW_RESUMO_EXECUTIVO_NEGOCIACOES\\G\""
echo ""

echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}💡 DICA: Copie e cole um dos comandos acima para executar rapidamente${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
