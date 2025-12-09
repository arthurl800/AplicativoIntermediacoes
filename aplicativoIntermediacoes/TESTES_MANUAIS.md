# 🧪 Guia de Testes Manuais - Dashboard de Negociações

## ✅ Pré-requisitos

Antes de executar os testes, verifique:

```bash
# 1. MySQL está rodando?
sudo service mysql status

# 2. PHP está disponível?
php --version

# 3. Servidor está rodando?
curl http://localhost:8000/index.php | grep -q "Sistema"
```

## 🚀 Teste 1: Acessar o Dashboard

### Passos:
1. Abra o navegador
2. Acesse: `http://localhost:8000/`
3. Faça login com:
   - Usuário: `admin`
   - Senha: `admin`
4. Clique em "Dashboard" (botão azul-claro no menu)

### Resultado Esperado:
- ✅ Página carrega sem erros
- ✅ 4 KPIs aparecem com números
- ✅ 3 gráficos aparecem com dados
- ✅ Botões de ação visíveis

---

## 🚀 Teste 2: Visualizar Gráficos

### Passos:
1. No Dashboard, observe cada gráfico:
   - **Gráfico 1**: Negociações por Operador (barras coloridas)
   - **Gráfico 2**: Negociações por Produto (pizza colorida)
   - **Gráfico 3**: Tendência 30 dias (linhas azul e verde)

2. Teste interatividade:
   - Passe o mouse sobre as barras/pontos
   - Clique na legenda para mostrar/ocultar séries
   - Zoom (scroll) no gráfico de tendência

### Resultado Esperado:
- ✅ Tooltip aparece ao passar o mouse
- ✅ Legenda é clicável e responsiva
- ✅ Dados são coerentes (não negativos, formatados)

---

## 🚀 Teste 3: Responsividade do Dashboard

### Desktop (1920x1080):
1. Abra o dashboard em resolução desktop
2. Observe:
   - KPIs em 4 colunas
   - Gráficos em 2 colunas (dois em cima, um embaixo)
   - Botões em linha

### Tablet (768x1024):
1. Redimensione o navegador para 768px de largura
2. Observe:
   - KPIs em 2 colunas
   - Gráficos em 1 coluna (empilhados)
   - Espaçamento confortável

### Mobile (375x667):
1. Abra em smartphone ou emule no F12 DevTools
2. Observe:
   - KPIs em 1 coluna
   - Gráficos em 1 coluna
   - Botões em coluna
   - Scroll vertical funciona

### Resultado Esperado:
- ✅ Layout se adapta a cada resolução
- ✅ Texto é legível em mobile
- ✅ Gráficos se redimensionam proporcionalmente
- ✅ Sem overflow horizontal

---

## 🚀 Teste 4: Página de Auditoria

### Passos:
1. No Dashboard, clique em "📋 Histórico de Auditoria"
2. Ou acesse diretamente: `index.php?controller=relatorio&action=auditoria`

### Verificações:
1. **Tabela deve exibir**:
   - Colunas: ID, Negociação, Ação, Usuário, Data, Descrição, Ações
   - Linhas com dados reais
   - Badges coloridas (INSERT=verde, UPDATE=amarelo, DELETE=vermelho)

2. **Paginação**:
   - Se houver mais de 50 registros, deve aparecer "Próxima"
   - Botões devem ser navegáveis

3. **Responsividade**:
   - Desktop: tabela completa
   - Mobile: transforma em cards com data-labels

### Resultado Esperado:
- ✅ Dados aparecem corretamente
- ✅ Paginação funciona
- ✅ Mobile vira cards
- ✅ Cores das badges são distintas

---

## 🚀 Teste 5: Exportar Relatório (CSV)

### Teste A: Download Automático
1. No Dashboard, clique em "📥 Exportar Relatório (CSV)"
2. Arquivo deve ser baixado automaticamente
3. Nome do arquivo: `relatorio_negociacoes_YYYYMMDD_HHMMSS.csv`

### Teste B: Com Período Customizado
1. Acesse URL diretamente:
   ```
   http://localhost:8000/index.php?controller=relatorio&action=exportarCSV&data_inicio=2025-12-01&data_fim=2025-12-31
   ```
2. Arquivo deve ser baixado

### Teste C: Validar Conteúdo
1. Abra o CSV em um editor de texto ou Excel
2. Verifique:
   - **Primeira linha**: Cabeçalhos (ID, Data Registro, Conta Vendedor, ...)
   - **BOM UTF-8**: Se abrir no Excel, acentos devem aparecer corretamente
   - **Separadores**: Dados separados por vírgula
   - **Datas**: Formatadas como DD/MM/YYYY HH:MM:SS
   - **Valores**: Formatados com R$ e vírgula decimal (ex: 1.234,56)

### Resultado Esperado:
- ✅ Download automático funciona
- ✅ Arquivo tem extensão .csv
- ✅ 19 colunas conforme documentação
- ✅ Dados formatados corretamente
- ✅ Excel abre sem problemas

---

## 🚀 Teste 6: Validações de Entrada

### Teste de Data Inválida
1. Acesse:
   ```
   http://localhost:8000/index.php?controller=relatorio&action=exportarCSV&data_inicio=31-12-2025&data_fim=2025-12-31
   ```
2. Deve retornar erro: "Datas inválidas"

### Teste de Período Futuro
1. Acesse:
   ```
   http://localhost:8000/index.php?controller=relatorio&action=exportarCSV&data_inicio=2099-01-01&data_fim=2099-12-31
   ```
2. Deve exportar vazio (sem erro, pois não há dados futuros)

### Resultado Esperado:
- ✅ Datas inválidas são rejeitadas
- ✅ Períodos futuros retornam vazio
- ✅ Não há erro 500 (tratamento robusto)

---

## 🚀 Teste 7: Autenticação e Segurança

### Teste A: Sem Autenticação
1. Limpe cookies do navegador (ou use modo incógnito)
2. Acesse: `index.php?controller=relatorio&action=dashboard`
3. Deve redirecionar para login

### Teste B: Com Autenticação
1. Faça login
2. Acesse o dashboard
3. Deve exibir conteúdo normalmente

### Resultado Esperado:
- ✅ Sem autenticação: redireciona para login
- ✅ Com autenticação: acesso permitido
- ✅ Logout funciona

---

## 🚀 Teste 8: Performance

### Teste de Carregamento
1. Abra o dashboard
2. Observe o tempo de carregamento (F12 → Network)
3. Esperado: < 1 segundo

### Teste com Muitos Dados
1. Simule 1000+ negociações no banco
2. Acesse o dashboard
3. Gráficos devem aparecer em < 500ms

### Resultado Esperado:
- ✅ Dashboard carrega em < 1s
- ✅ Gráficos renderizam em < 500ms
- ✅ Sem lag na interação com gráficos
- ✅ Paginação é responsiva

---

## 🚀 Teste 9: Navegadores Diferentes

Teste em cada navegador:

### Chrome/Edge/Firefox (Desktop)
```bash
# Dashboard
✓ Gráficos aparecem
✓ Responsividade funciona
✓ Tooltips aparecem
✓ CSV é baixado
```

### Safari (Mac/iOS)
```bash
# Verifique:
✓ Cores aparecem corretamente
✓ Textos estão centralizados
✓ Gráficos não ficam cortados
```

### Mobile Browsers
```bash
# Em smartphone real ou emulado:
✓ Layout mobile aparece
✓ Cards são legíveis
✓ Botões são clicáveis (tamanho 44x44px mínimo)
✓ Scroll funciona suave
```

---

## 🚀 Teste 10: Integração com Banco de Dados

### Verificar Views SQL
```bash
mysql -u INTERMEDIACOES_USER -p'%intermediacoes999$#' -h localhost INTERMEDIACOES

# No MySQL prompt, execute:
SHOW TABLES LIKE 'VW_%';

# Resultado esperado:
# VW_NEGOCIACOES_POR_DATA
# VW_NEGOCIACOES_POR_OPERADOR
# VW_NEGOCIACOES_POR_PRODUTO
# VW_RESUMO_EXECUTIVO_NEGOCIACOES
```

### Verificar Dados
```bash
# Verifique se existe alguma negociação
SELECT COUNT(*) FROM NEGOCIACOES;

# Se houver dados, as views devem retornar números
SELECT * FROM VW_RESUMO_EXECUTIVO_NEGOCIACOES;
```

---

## 📋 Checklist de Testes

- [ ] Dashboard carrega sem erro 500
- [ ] 4 KPIs exibem números
- [ ] 3 gráficos aparecem com dados
- [ ] Gráficos são interativos
- [ ] Dashboard é responsivo (3 tamanhos)
- [ ] Página de Auditoria carrega
- [ ] Tabela de auditoria exibe dados
- [ ] Paginação funciona
- [ ] CSV é baixado
- [ ] CSV tem 19 colunas
- [ ] Datas estão DD/MM/YYYY
- [ ] Valores estão R$ formatado
- [ ] Validação de datas funciona
- [ ] Sem autenticação: redireciona
- [ ] Com autenticação: acesso permitido
- [ ] Dashboard < 1s de carregamento
- [ ] Works em Chrome, Firefox, Safari
- [ ] Works em desktop, tablet, mobile
- [ ] MySQL views existem
- [ ] Dados MySQL estão acessíveis

---

## 🐛 Troubleshooting

### Problema: "Database Connection Error"
```bash
# Solução:
sudo service mysql restart
mysql -u INTERMEDIACOES_USER -p'%intermediacoes999$#' -h localhost INTERMEDIACOES -e "SELECT 1;"
```

### Problema: "View not found"
```bash
# Solução:
mysql -u INTERMEDIACOES_USER -p'%intermediacoes999$#' -h localhost INTERMEDIACOES < /var/www/html/aplicativoIntermediacoes/database/setup_analytics.sql
```

### Problema: Gráficos em branco
```bash
# Verifique no navegador (F12 Console):
# Se houver erro de CDN, Chart.js não foi carregado
# Solução: Verificar conexão internet ou hospedar Chart.js localmente
```

### Problema: CSV não baixa
```bash
# Verifique no navegador console se há erros
# Solução: Verificar permissões de header() em PHP
php -r "ini_get('output_buffering');"  # Deve ser 0 ou tudo
```

---

## ✅ Teste de Conclusão

Se todos os testes passarem, o sistema está 100% funcional!

Execute este script para confirmar:

```bash
php -l /var/www/html/aplicativoIntermediacoes/app/controller/RelatorioController.php && \
php -l /var/www/html/aplicativoIntermediacoes/app/model/AuditoriaModel.php && \
php -l /var/www/html/aplicativoIntermediacoes/app/view/relatorio/dashboard.php && \
php -l /var/www/html/aplicativoIntermediacoes/app/view/relatorio/auditoria.php && \
echo "✅ Todos os arquivos validados com sucesso!"
```

Resultado esperado:
```
No syntax errors detected in .../RelatorioController.php
No syntax errors detected in .../AuditoriaModel.php
No syntax errors detected in .../dashboard.php
No syntax errors detected in .../auditoria.php
✅ Todos os arquivos validados com sucesso!
```

---

**Documento criado**: Dezembro 2025  
**Versão**: 1.0  
**Status**: Pronto para testes em produção
