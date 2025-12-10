# Fluxo Completo de Negociações - Documentação Sistema

## 📋 Resumo da Implementação

Sistema de negociação de títulos financeiros que permite:
1. **Importação** de dados em planilha CSV/XLSX para banco MySQL
2. **Visualização** dos títulos disponíveis no Painel de Negociações
3. **Negociação** de títulos com cálculos automáticos de valores e margens
4. **Persistência** de negociações com atualização automática de quantidades
5. **Rastreamento** em tabela separada de títulos negociados

---

## 🔄 Fluxo de Dados

```
┌─────────────────────────────────────────────────────────────┐
│ 1. IMPORTAÇÃO (CSV/XLSX)                                    │
└──────────────────────┬──────────────────────────────────────┘
                       ↓
        ┌──────────────────────────────┐
        │ INTERMEDIACOES_TABLE         │
        │ (títulos disponíveis)        │
        │ Quantidade > 0               │
        └──────────────────┬───────────┘
                           ↓
        ┌──────────────────────────────┐
        │ 2. PAINEL DE NEGOCIAÇÕES     │
        │ (Negocia\u00e7\u00f5es > Painel)    │
        │ Lista com filtros/agrega\u00e7\u00e3o │
        └──────────────────┬───────────┘
                           ↓
        ┌──────────────────────────────┐
        │ 3. FORMUL\u00c1RIO DE NEGOCIA\u00c7\u00c3O  │
        │ (Clicar em "Negociar")       │
        │ Dados pr\u00e9-preenchidos       │
        └──────────────────┬───────────┘
                           ↓
        ┌──────────────────────────────┐
        │ 4. PROCESSAMENTO             │
        │ ✓ C\u00e1lculos server-side      │
        │ ✓ Valida\u00e7\u00f5es              │
        └──────────────────┬───────────┘
                           ↓
            ┌──────────────────────────┐
            │ INSERT em NEGOCIACOES   │
            │ (detalhe completo)       │
            └──────────────────┬───────┘
                               ↓
            ┌──────────────────────────┐
            │ UPDATE em               │
            │ INTERMEDIACOES_TABLE    │
            │ (decrementa Quantidade)  │
            └──────────────────┬───────┘
                               ↓
            ┌──────────────────────────────┐
            │ INSERT em                    │
            │ INTERMEDIACOES_TABLE_NEGOCIADA
            │ (cópia com qtd negociada)    │
            └──────────────────┬──────────┘
                               ↓
        ┌──────────────────────────────┐
        │ 5. VISUALIZAÇ\u00c3O            │
        │ Negociadas >                 │
        │ Intermediações Negociadas    │
        └──────────────────────────────┘
```

---

## 📦 Estrutura de Dados

### **INTERMEDIACOES_TABLE** (Fonte)
- Contém títulos importados do CSV/XLSX
- Campo `Quantidade` reflete títulos **disponíveis para venda**
- Decrementada a cada negociação

**Campos principais:**
```
id, Conta, Nome, Ativo, Produto, Vencimento, Quantidade,
Valor_Bruto, Valor_Liquido, Estrategia, Escritorio, ID_Registro
```

### **NEGOCIACOES** (Log de Negociações)
- Detalhe completo de cada negociação realizada
- Calcula e persiste: preços, ganhos, rentabilidades por ator

**Campos principais:**
```
id, Data_Registro, Conta_Vendedor, Nome_Vendedor, Produto, 
Quantidade_negociada, Valor_Bruto_Importado_Raw,
Taxa_Saida, Valor_Bruto_Saida, Valor_Liquido_Saida, 
Preco_Unitario_Saida, Ganho_Saida, Rentabilidade_Saida,
Conta_Comprador, Nome_Comprador, Taxa_Entrada, Valor_Bruto_Entrada,
Preco_Unitario_Entrada, Valor_Plataforma,
Corretagem_Assessor, Roa_Assessor
```

### **INTERMEDIACOES_TABLE_NEGOCIADA** (Histórico)
- Espelho de títulos que **foram negociados**
- Quantidade reflete apenas o volume **vendido**
- Valores proporcionais ao volume negociado

---

## 🧮 Cálculos Automáticos

### **Vendedor (Saída)**
```
Bruto de Saída       = Unitário importado × Quantidade vendida
                       (ou valor explicitamente informado)

Líquido de Saída     = Bruto de Saída × (1 - Taxa_Saída%)

Preço Unitário       = Líquido de Saída ÷ Quantidade vendida

Custo Importado      = Unitário importado × Quantidade vendida

Ganho                = Líquido de Saída - Custo Importado

Rentabilidade        = (Ganho ÷ Custo Importado) × 100%
```

### **Comprador (Entrada)**
```
Preço Unitário       = Valor de Entrada ÷ Quantidade vendida
```

### **Assessor (Plataforma)**
```
Corretagem           = Valor da Plataforma (informado)

ROA (%)              = (Corretagem ÷ Valor de Entrada) × 100%
```

---

## 🛠️ Componentes da Aplicação

### **Controllers**
- **NegociacaoController** → `painel()`, `formulario()`, `processar()`
- **DataController** → listas, filtros, agregações

### **Models**
- **NegociacaoModel** → CRUD em NEGOCIACOES, cálculos, consultas em INTERMEDIACOES_TABLE
- **IntermediacoesNegociadaModel** → transferência para INTERMEDIACOES_TABLE_NEGOCIADA

### **Views**
- `app/view/negociacoes/painel.php` → lista com aggregations
- `app/view/negociacoes/formulario.php` → entrada de dados + preview
- `app/view/dados/visualizacao_negociadas.php` → histórico pós-negociação

### **Rotas**
```
?controller=negociacao&action=painel           → Painel de Negociações
?controller=negociacao&action=formulario&id=X  → Abre formulário para ID X
(POST) processar()                              → Persiste negociação
```

---

## ✅ Fluxo Verificado (Teste Integrado)

Executado em `tests/test_negotiation_flow.php`:

```
[✓] Prepara registro fonte com Quantidade > 0
[✓] Simula submissão de formulário (POST)
[✓] Calcula valores server-side (segurança)
[✓] Insere em NEGOCIACOES com ID X
[✓] Decrementa Quantidade em INTERMEDIACOES_TABLE
[✓] Transfere para INTERMEDIACOES_TABLE_NEGOCIADA com qtd proporcionalmente negociada
[✓] Verifica consistência entre tabelas
```

**Resultado:**
- NEGOCIACOES: novo registro com cálculos
- INTERMEDIACOES_TABLE: Quantidade decrementada
- INTERMEDIACOES_TABLE_NEGOCIADA: nova linha com volume negociado

---

## 🔒 Segurança

1. **Cálculos Server-Side**: Todos os valores financeiros recalculados no servidor, não confiando em POST do cliente
2. **Validações**:
   - Quantidade vendida ≤ Quantidade disponível
   - Valores monetários positivos
   - Usuário autenticado
3. **Proteção contra overflow**: Valores em centavos convertidos corretamente

---

## 📝 Próximas Etapas Opcionais

### Dashboard com Gráficos
- Volume negociado por período
- Rentabilidade média
- TOP 10 títulos mais negociados
- Comparativo vendedor vs comprador

### Auditoria Completa
- Tabela `AUDIT_LOG` com user_id, timestamp, ação (INSERT/UPDATE/DELETE)
- View admin para consultar logs
- Rastreamento de modificações pós-negociação

### Relatorios Avançados
- Exportação em PDF/Excel
- Filtros por período, vendedor, produto
- KPIs consolidados

---

## 🚀 Como Usar

### 1. Importar Dados
```
Clique em "Upload" → Selecione CSV/XLSX → Importar
```

### 2. Visualizar Painel
```
Clique em "Negociações" → "Painel de Negociações"
```

### 3. Negociar um Título
```
Clique em "Negociar" na linha desejada
→ Preencha dados (Vendedor, Comprador, Assessor)
→ Revise preview
→ Clique em "Confirmar Negociação"
```

### 4. Acompanhar Negociações
```
Clique em "Negociadas" → "Intermediações Negociadas"
```

---

## 📊 Estado Atual

| Tabela | Status | Registros |
|--------|--------|-----------|
| INTERMEDIACOES_TABLE | ✅ Ativa | ~100+ (depende importação) |
| NEGOCIACOES | ✅ Ativa | Log completo de vendas |
| INTERMEDIACOES_TABLE_NEGOCIADA | ✅ Ativa | Espelho negociado |

**Verificação rápida:**
```bash
php scripts/check_db.php
```

---

**Última atualização:** 10 de dezembro de 2025
