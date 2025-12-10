# 🎯 Guia Rápido - Sistema de Negociações

## ✅ O Sistema Agora Funciona Completamente!

Todas as etapas do fluxo foram implementadas e testadas:

```
┌─────────────────────────────────────────────────────────────┐
│ PASSO 1: Importar Planilha (CSV/XLSX)                       │
│ → Dados entram em INTERMEDIACOES_TABLE                      │
├─────────────────────────────────────────────────────────────┤
│ PASSO 2: Visualizar Painel de Negociações                   │
│ → Mostra títulos disponíveis (Quantidade > 0)              │
├─────────────────────────────────────────────────────────────┤
│ PASSO 3: Selecionar Título e Clicar em "Negociar"          │
│ → Abre formulário com dados pré-preenchidos                │
├─────────────────────────────────────────────────────────────┤
│ PASSO 4: Preencher Dados da Negociação                      │
│ → Vendedor: Taxa Saída, Valor Bruto/Líquido               │
│ → Comprador: Taxa Entrada, Valor de Entrada               │
│ → Assessor: Valor da Plataforma (comissão)                 │
├─────────────────────────────────────────────────────────────┤
│ PASSO 5: Processar Negociação (Server-Side)                │
│ ✓ Calcula Preços Unitários                                  │
│ ✓ Calcula Ganhos e Rentabilidades                           │
│ ✓ Calcula Corretagem e ROA                                  │
│ ✓ Salva em NEGOCIACOES (detalhe completo)                   │
│ ✓ Decrementa Quantidade em INTERMEDIACOES_TABLE            │
│ ✓ Transfere para INTERMEDIACOES_TABLE_NEGOCIADA            │
└─────────────────────────────────────────────────────────────┘
```

---

## 📍 Acessos da Aplicação

### **Upload**
```
http://localhost:8000/?controller=upload&action=index
```
- Selecione arquivo CSV ou XLSX com dados de intermediações
- Clique "Importar" → insere em INTERMEDIACOES_TABLE

### **Painel de Negociações**
```
http://localhost:8000/?controller=negociacao&action=painel
```
- Lista títulos disponíveis (Quantidade > 0)
- Clique em "Negociar" para abrir formulário de venda

### **Intermediações Negociadas**
```
http://localhost:8000/?controller=dados&action=visualizar_negociadas
```
- Histórico de negociações (pós-venda)
- Mostra volume, valores e datas

---

## 💡 Campos da Negociação

### **Vendedor (Saída)**
| Campo | Entrada | Cálculo |
|-------|---------|---------|
| Taxa de Saída (%) | ✏️ Você informa | - |
| Valor Bruto de Saída | ✏️ Ou automático | Unitário × Quantidade |
| Valor Líquido de Saída | ✏️ Ou automático | Bruto × (1 - Taxa%) |
| **Preço Unitário** | 📊 Cálculo | Líquido ÷ Quantidade |
| **Ganho** | 📊 Cálculo | Líquido - Custo Importado |
| **Rentabilidade (%)** | 📊 Cálculo | (Ganho ÷ Custo) × 100 |

### **Comprador (Entrada)**
| Campo | Entrada | Cálculo |
|-------|---------|---------|
| Taxa de Entrada (%) | ✏️ Você informa | - |
| Valor de Entrada | ✏️ Você informa | - |
| **Preço Unitário** | 📊 Cálculo | Valor ÷ Quantidade |

### **Assessor/Plataforma**
| Campo | Entrada | Cálculo |
|-------|---------|---------|
| Valor da Plataforma | ✏️ Você informa | (comissão) |
| **Corretagem** | 📊 Cálculo | = Valor Plataforma |
| **ROA (%)** | 📊 Cálculo | (Corretagem ÷ Valor Entrada) × 100 |

---

## 🧪 Teste Rápido da Funcionalidade

```bash
# 1. Verificar estado do banco
cd /var/www/html/aplicativoIntermediacoes
php scripts/check_db.php

# 2. Rodar teste integrado completo
php tests/test_negotiation_flow.php
```

**Esperado:**
```
✓ NEGOCIACOES row inserted
✓ INTERMEDIACOES_TABLE quantity updated correctly
✓ INTERMEDIACOES_TABLE_NEGOCIADA rows inserted
✅ TEST COMPLETED SUCCESSFULLY
```

---

## 🔍 Verificações no Banco

### INTERMEDIACOES_TABLE (Fonte)
```sql
-- Títulos disponíveis
SELECT id, Conta, Nome, Ativo, Quantidade 
FROM INTERMEDIACOES_TABLE 
WHERE Quantidade > 0;
```

### NEGOCIACOES (Log Completo)
```sql
-- Detalhe de cada negociação
SELECT id, Data_Registro, Conta_Vendedor, Quantidade_negociada,
       Valor_Liquido_Saida, Ganho_Saida, Rentabilidade_Saida
FROM NEGOCIACOES
ORDER BY Data_Registro DESC;
```

### INTERMEDIACOES_TABLE_NEGOCIADA (Histórico)
```sql
-- Títulos negociados (volume vendido)
SELECT id, Conta, Nome, Ativo, Quantidade, Valor_Bruto
FROM INTERMEDIACOES_TABLE_NEGOCIADA
ORDER BY Data_Importacao DESC;
```

---

## ⚙️ Configurações

Arquivo: `/var/www/html/aplicativoIntermediacoes/config/database.php`

```php
return [
    'DB_HOST'     => 'localhost',
    'DB_NAME'     => 'INTERMEDIACOES',
    'DB_USER'     => 'INTERMEDIACOES_USER',
    'DB_PASS'     => '%intermediacoes999$#',
    'DB_CHARSET'  => 'utf8mb4',
    'TABLE_NAME'  => 'INTERMEDIACOES_TABLE',   // Fonte
    'USER_TABLE'  => 'USUARIOS_TABLE'          // Usuários
];
```

---

## 🚀 Iniciando o Servidor (Desenvolvimento)

```bash
cd /var/www/html/aplicativoIntermediacoes

# Iniciar servidor PHP local
php -S localhost:8000 -t .

# Em outro terminal, testar
curl http://localhost:8000/?controller=negociacao&action=painel
```

---

## 📋 Checklist Final

- [x] Upload/Importação CSV/XLSX → INTERMEDIACOES_TABLE
- [x] Painel de Negociações mostra títulos disponíveis
- [x] Formulário pré-preenchido com dados
- [x] Cálculos automáticos server-side (segurança)
- [x] Persiste em NEGOCIACOES
- [x] Atualiza Quantidade em INTERMEDIACOES_TABLE
- [x] Transfere para INTERMEDIACOES_TABLE_NEGOCIADA
- [x] Teste integrado validando fluxo completo
- [ ] Dashboard com gráficos (opcional)
- [ ] Auditoria/Log de usuário (opcional)

---

## 📞 Suporte Rápido

### Erro: "Quantidade não pode ser maior que disponível"
→ Verifique se há registros com Quantidade > 0 em INTERMEDIACOES_TABLE

### Erro: "Negociação não encontrada"
→ Certifique-se de que o ID passado existe e tem Quantidade > 0

### INTERMEDIACOES_TABLE_NEGOCIADA vazia
→ Verifique se transfer foi chamado com `source_id` correto (implementado em NegociacaoController)

### Valores incorretos nos cálculos
→ Todos os valores são recalculados server-side. Verifique logs:
```bash
tail -f /var/log/php-errors.log
```

---

**Status:** ✅ Sistema Operacional  
**Última atualização:** 10 de dezembro de 2025  
**Versão:** 1.0 - Fluxo Completo
