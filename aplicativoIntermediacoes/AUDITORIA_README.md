# Sistema de Gerenciamento de Negociações - Documentação de Auditoria e Dashboard

## Visão Geral

O sistema agora possui:
- ✅ **Dashboard de Negociações**: Visualizações em tempo real com gráficos interativos
- ✅ **Histórico de Auditoria**: Rastreamento completo de alterações nas negociações
- ✅ **Relatórios em CSV**: Exportação de dados para análise externa
- ✅ **Views Analíticas**: 4 views SQL para diferentes perspectivas dos dados

## Componentes Instalados

### 1. Tabelas e Views do Banco de Dados

#### Tabela: `NEGOCIACOES_AUDITORIA`
Rastreia todas as alterações nas negociações com:
- `id`: ID único da auditoria
- `negociacao_id`: Referência ao ID da negociação
- `acao`: Tipo de ação (INSERT, UPDATE, DELETE)
- `usuario_name`: Usuário que realizou a ação
- `data_acao`: Data/hora da ação
- `dados_antes`: JSON com valores antes da alteração
- `dados_depois`: JSON com valores depois da alteração
- `descricao_mudanca`: Descrição legível da mudança

#### Views Analíticas (4 views):

1. **VW_NEGOCIACOES_POR_OPERADOR**
   - Agrupa negociações por operador/vendedor
   - Calcula: total, quantidade, valor, corretagem, ROA e rentabilidade
   - Ordenado por valor total descendente

2. **VW_NEGOCIACOES_POR_PRODUTO**
   - Agrupa negociações por tipo de produto
   - Métricas similares ao anterior
   - Útil para análise de mix de produtos

3. **VW_NEGOCIACOES_POR_DATA**
   - Agrupa por data de negociação
   - Mostra tendências diárias
   - Útil para análise de volume ao longo do tempo

4. **VW_RESUMO_EXECUTIVO_NEGOCIACOES**
   - KPIs principais do sistema
   - Total de negociações, valor total, média, ROA, rentabilidade
   - Métricas de diversificação (clientes únicos, produtos únicos)

### 2. Controlador: `RelatorioController`

**Métodos disponíveis:**

- **`dashboard()`**: Exibe o dashboard com gráficos e KPIs
  - Rota: `index.php?controller=relatorio&action=dashboard`
  - Acesso: Usuários autenticados

- **`auditoria()`**: Exibe histórico de auditoria com paginação
  - Rota: `index.php?controller=relatorio&action=auditoria`
  - Acesso: Usuários autenticados
  - Paginação: 50 registros por página

- **`exportarCSV()`**: Exporta relatório de negociações em CSV
  - Rota: `index.php?controller=relatorio&action=exportarCSV`
  - Parâmetros opcionais: `data_inicio`, `data_fim` (formato YYYY-MM-DD)
  - Padrão: Últimos 30 dias
  - Download automático do arquivo

### 3. Views (Templates)

#### `app/view/relatorio/dashboard.php`
- Resumo executivo com 4 KPIs (Total de Negociações, Valor Total, Quantidade, Clientes Únicos)
- 3 Gráficos interativos usando Chart.js:
  - Negociações por Operador (bar chart)
  - Negociações por Produto (doughnut chart)
  - Tendência de Negociações (line chart - últimos 30 dias)
- Responsivo para mobile (breakpoint 1280px)
- Botões de exportação

#### `app/view/relatorio/auditoria.php`
- Tabela com histórico de auditoria
- Colunas: ID, ID Negociação, Ação, Usuário, Data/Hora, Descrição, Ações
- Paginação
- Responsivo para mobile

### 4. Modelo: `AuditoriaModel`

**Métodos disponíveis:**

```php
// Estatísticas por operador
$stats = $auditoriaModel->getEstatisticasPorOperador();

// Estatísticas por produto
$stats = $auditoriaModel->getEstatisticasPorProduto();

// Estatísticas dos últimos N dias
$stats = $auditoriaModel->getEstatisticasPorData(30);

// Resumo executivo (KPIs principais)
$kpis = $auditoriaModel->getResumoExecutivo();

// Auditoria de uma negociação específica
$audit = $auditoriaModel->getAuditoriaParaNegociacao($negociacao_id);

// Auditoria completa com paginação
$audit = $auditoriaModel->getAuditoriaCompleta($limit, $offset);

// Estatísticas de um período específico
$stats = $auditoriaModel->getEstatisticasPorPeriodo('2025-12-01', '2025-12-31');
```

## Como Usar

### 1. Acessar o Dashboard
1. Faça login no sistema
2. Clique em "Dashboard" no menu de navegação
3. Visualize os gráficos e KPIs
4. Use os botões de exportação para baixar relatórios

### 2. Consultar Auditoria
1. Clique em "Auditoria" no menu
2. Veja o histórico de negociações
3. Use "Ver Detalhes" para mais informações
4. Navegue pelas páginas usando os botões de paginação

### 3. Exportar Relatório
**Opção 1: Via Interface**
- Clique em "📥 Exportar Relatório (CSV)" no Dashboard
- O arquivo será baixado automaticamente

**Opção 2: Especificar Período**
- Use a URL: `index.php?controller=relatorio&action=exportarCSV&data_inicio=2025-12-01&data_fim=2025-12-31`
- Formato de data: YYYY-MM-DD

### 4. Filtrar e Analisar
- Os gráficos são interativos (hover, zoom, etc)
- Os dados são atualizados em tempo real
- Use o CSV exportado para análises em Excel/Google Sheets

## Estrutura de Dados

### Formato de Exportação CSV
O CSV inclui as seguintes colunas:
- ID
- Data Registro (DD/MM/YYYY HH:MM:SS)
- Conta Vendedor
- Nome Vendedor
- Produto
- Estratégia
- Quantidade Negociada
- Valor Bruto Saída (R$)
- Valor Líquido Saída (R$)
- Preço Unitário Saída (R$)
- Ganho Saída (R$)
- Rentabilidade Saída (%)
- Conta Comprador
- Nome Comprador
- Taxa Entrada (%)
- Valor Bruto Entrada (R$)
- Preço Unitário Entrada (R$)
- Corretagem Assessor (R$)
- ROA Assessor (%)

## Integração com Sistema Existente

### Menu de Navegação
Os seguintes links foram adicionados ao header:
- **Dashboard** (cor: azul-claro): Acessa o painel de análise
- **Auditoria** (cor: padrão): Acessa o histórico de auditoria

### Fluxo de Negociação
A auditoria é integrada naturalmente:
1. Usuário realiza negociação → Salva em `NEGOCIACOES`
2. Automaticamente → Registra em `NEGOCIACOES_AUDITORIA` (manual via PHP atualmente)
3. Dashboard → Agrega dados via views SQL
4. Relatórios → Exporta dados para análise

## Tecnologias Utilizadas

- **Backend**: PHP 8+ com PDO
- **Banco de Dados**: MySQL 8+ com Views
- **Frontend**: HTML5, CSS3, JavaScript
- **Gráficos**: Chart.js v3.9.1
- **Responsividade**: Media queries (breakpoint 1280px)

## Notas Importantes

### Triggers Opcionais
Os triggers MySQL (INSERT/UPDATE/DELETE) não foram instalados devido a limitações de permissões. 
Alternativas:
- ✅ Usar log manual em PHP (implementado em `DataController`)
- ✅ Solicitar ao DBA para habilitar `log_bin_trust_function_creators`
- ✅ Usar views para análise (já implementado)

### Performance
- Views SQL são otimizadas com índices
- Paginação no histórico de auditoria (50 registros/página)
- Gráficos limitados a 30 dias por padrão

### Segurança
- Acesso restrito a usuários autenticados
- Validação de datas (YYYY-MM-DD)
- Prepared statements para todas as queries
- Escape de HTML em outputs

## Próximos Passos (Opcional)

1. **PDF Export**: Usar TCPDF para gerar relatórios em PDF
2. **Email**: Agendar envio de relatórios por email
3. **Permissões Granulares**: Restringir relatórios por operador/produto
4. **Alertas**: Notificar sobre anomalias (ex: valor alto, operador novo)
5. **Cache**: Cachear views para melhor performance

## Suporte

Para problemas, verifique:
1. MySQL está rodando: `sudo service mysql status`
2. Banco de dados existe: `mysql -u user -p -e "SHOW DATABASES;"`
3. Views existem: `mysql -u user -p DB -e "SHOW TABLES LIKE 'VW_%';"`
4. Logs do PHP: `/var/log/php/error.log` ou browser console

---

**Última atualização**: Dezembro 2025  
**Versão**: 1.0 com Dashboard e Auditoria
