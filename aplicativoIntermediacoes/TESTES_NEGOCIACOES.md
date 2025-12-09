# 🧪 Testes e Validação - Painel de Negociações

## 📋 Checklist de Validação

### ✅ Arquivos Criados/Modificados

```
[✓] app/controller/NegociacaoController.php          (NOVO)
[✓] app/model/NegociacaoModel.php                   (EXPANDIDO)
[✓] app/view/negociacoes/painel.php                 (NOVO)
[✓] app/view/negociacoes/formulario.php             (NOVO)
[✓] index.php                                        (MODIFICADO)
[✓] includes/header.php                              (MODIFICADO)
[✓] README.md                                        (ATUALIZADO)
[✓] NEGOCIACOES.md                                   (NOVO)
[✓] GUIA_RAPIDO_NEGOCIACOES.md                       (NOVO)
[✓] COMPONENTES_CSS.md                               (NOVO)
[✓] RESUMO_IMPLEMENTACAO.md                          (NOVO)
```

### ✅ Validações Sintáticas

```bash
# Comando executado
php -l app/model/NegociacaoModel.php
# ✓ Resultado: No syntax errors detected

php -l app/controller/NegociacaoController.php
# ✓ Resultado: No syntax errors detected

php -l index.php
# ✓ Resultado: No syntax errors detected

php -l includes/header.php
# ✓ Resultado: No syntax errors detected

php -l app/view/negociacoes/painel.php
# ✓ Resultado: No syntax errors detected

php -l app/view/negociacoes/formulario.php
# ✓ Resultado: No syntax errors detected
```

---

## 🚀 Teste Manual Completo

### Pré-requisitos
- [ ] MySQL rodando em localhost:3306
- [ ] Banco de dados `INTERMEDIACOES` criado
- [ ] Tabela `INTERMEDIACOES_TABLE` com dados
- [ ] Tabela `USUARIOS_TABLE` com admin/admin
- [ ] Servidor PHP rodando em localhost:8000

### Teste 1: Verificar Link no Menu

**Passo:**
1. Acesse http://localhost:8000
2. Procure no header por "💰 Negociações"

**Resultado Esperado:**
- Link visível e clicável
- Está entre "📥 Importar" e "✅ Negociadas"

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 2: Acessar Painel Direto pela URL

**Passo:**
1. Acesse URL: `http://localhost:8000/index.php?controller=negociacao&action=painel`

**Resultado Esperado:**
- [ ] Painel carrega sem erro 404
- [ ] Exibe título "💰 Painel de Negociações"
- [ ] Exibe descrição "Gerencie as intermediações disponíveis"

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 3: Verificar Dados na Tabela

**Passo:**
1. Acesse painel de negociações
2. Verifique primeira linha da tabela

**Resultado Esperado:**
- [ ] Coluna "Conta" preenchida
- [ ] Coluna "Cliente" preenchida
- [ ] Coluna "Produto" com badge
- [ ] Coluna "Qtd." com número
- [ ] Coluna "Vl. Bruto" em formato R$ (ex: R$ 5.000,00)
- [ ] Coluna "Vl. Líquido" em formato R$ (ex: R$ 4.750,00)
- [ ] Coluna "Vencimento" em formato DD/MM/AAAA (ex: 15/03/2025)

**Validação de Conversão:**
- [ ] Datas: 2025-03-15 → 15/03/2025
- [ ] Valores: 500000 → R$ 5.000,00
- [ ] Percentuais: 575 → 5,75%

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 4: Clicar em "Negociar"

**Passo:**
1. No painel, clique em botão "🤝 Negociar" de qualquer linha
2. Espere redirecionamento

**Resultado Esperado:**
- [ ] URL muda para: `...&action=formulario&id=X`
- [ ] Título: "🤝 Formulário de Negociação"
- [ ] Dois painéis aparecem (dados + valores)

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 5: Verificar Dados Pré-preenchidos

**Passo:**
1. No formulário de negociação
2. Verifique painel esquerdo ("Dados da Intermediação")
3. Verifique painel direito ("Valores e Quantidades")

**Resultado Esperado:**

**Painel Esquerdo:**
- [ ] Conta: Preenchida com conta do cliente
- [ ] Nome do Cliente: Preenchido
- [ ] Produto: Em badge colorida
- [ ] Estratégia: Preenchida
- [ ] Emissor (CNPJ): Preenchido
- [ ] Vencimento: Formato DD/MM/AAAA

**Painel Direito:**
- [ ] Qtd Disponível: Mostra número
- [ ] Taxa (%): Mostra percentual formatado (X,XX%)
- [ ] Vl. Bruto: Mostra valor em R$ (R$ X.XXX,XX)
- [ ] IR: Mostra valor em R$ (R$ X.XXX,XX)
- [ ] Vl. Líquido: Mostra valor em R$ (R$ X.XXX,XX)
- [ ] Data Compra: Formato DD/MM/AAAA

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 6: Validação de Quantidade - Mínimo

**Passo:**
1. No formulário, deixe campo "Quantidade a Vender" vazio
2. Clique em "✓ Confirmar Venda"

**Resultado Esperado:**
- [ ] Validação client-side: campo marca em vermelho
- [ ] Mensagem: "Quantidade inválida" ou similar
- [ ] Não submete o formulário

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 7: Validação de Quantidade - Máximo

**Passo:**
1. No formulário, insira número maior que quantidade disponível
   - Ex: Quantidade disponível = 10, insira 15

**Resultado Esperado:**
- [ ] Campo fica vermelho
- [ ] Campo "Qtd Remanescente" mostra "Quantidade inválida!"
- [ ] Background do campo remanescente fica vermelho
- [ ] Não deixa submeter (validação client + server)

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 8: Cálculo Automático de Remanescente

**Passo:**
1. No formulário, insira quantidade válida
   - Ex: Disponível = 10, insira 6

**Resultado Esperado:**
- [ ] Campo "Qtd Remanescente" atualiza automaticamente
- [ ] Mostra: 10 - 6 = 4
- [ ] Campo fica branco/normal
- [ ] Sem erro exibido

**Adicionar mais valores:**
- [ ] Insira 3 → Mostra 7
- [ ] Insira 10 → Mostra 0
- [ ] Insira 1 → Mostra 9

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 9: Confirmar Venda com Sucesso

**Passo:**
1. Insira quantidade válida (ex: 3 de 10)
2. Clique em "✓ Confirmar Venda"

**Resultado Esperado:**
- [ ] Redireciona para painel
- [ ] Exibe alerta verde: "✅ Negociação realizada com sucesso!"
- [ ] Mostra: "Quantidade vendida: 3. Quantidade remanescente: 7"
- [ ] Volta para tabela do painel
- [ ] Quantidade na tabela diminuiu (era 10, agora 7)

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 10: Cancelar Formulário

**Passo:**
1. Abra formulário
2. Clique em botão "← Cancelar"

**Resultado Esperado:**
- [ ] Redireciona para painel de negociações
- [ ] Nenhuma alteração foi feita no BD
- [ ] Quantidade continua a mesma na tabela

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 11: Filtrar por Cliente

**Passo:**
1. No painel, preencha campo "Cliente"
2. Insira nome parcial (ex: "Banco")
3. Clique em "🔍 Filtrar"

**Resultado Esperado:**
- [ ] Tabela atualiza
- [ ] Exibe apenas intermediações com esse cliente
- [ ] URL muda para incluir parâmetro: `?cliente=Banco`

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 12: Filtrar por Produto

**Passo:**
1. No painel, preencha campo "Produto"
2. Insira código parcial (ex: "LCA")
3. Clique em "🔍 Filtrar"

**Resultado Esperado:**
- [ ] Tabela atualiza
- [ ] Exibe apenas intermediações com esse produto
- [ ] URL muda para incluir parâmetro: `?produto=LCA`

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 13: Verificar Responsividade - Desktop

**Passo:**
1. Abra em resolução desktop (1920x1080)
2. Verifique layout

**Resultado Esperado:**
- [ ] Tabela exibe todas as 9 colunas
- [ ] Formulário em 2 colunas (dados + valores)
- [ ] Sem scroll horizontal
- [ ] Tudo legível

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 14: Verificar Responsividade - Tablet

**Passo:**
1. Abra em resolução tablet (768x1024)
2. Verifique layout

**Resultado Esperado:**
- [ ] Tabela ainda exibida
- [ ] Colunas se adaptam
- [ ] Pode ter scroll horizontal se necessário
- [ ] Formulário em 1 coluna (empilhado)
- [ ] Ainda legível

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 15: Verificar Responsividade - Mobile

**Passo:**
1. Abra em resolução mobile (375x667)
2. Verifique layout

**Resultado Esperado:**
- [ ] Tabela adaptada
- [ ] Pode ter scroll horizontal
- [ ] Formulário em 1 coluna
- [ ] Botões clicáveis
- [ ] Texto legível

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 16: Erros de Conexão

**Passo:**
1. Pausar MySQL (ou desconectar internet)
2. Tente acessar painel

**Resultado Esperado:**
- [ ] Exibe erro: "Erro ao carregar negociações"
- [ ] Link "Voltar ao Painel" disponível
- [ ] Não crashes

**Status:** [ ] Passar / [ ] Falhar

---

## 🧮 Validações de Negócio

### Cenário 1: Venda Parcial

```
Intermediação A:
  Quantidade: 10
  
Venda 1: Vende 3
  Remanescente: 10 - 3 = 7
  
Venda 2: Vende 5
  Remanescente: 7 - 5 = 2
  
BD Final: Quantidade = 2

Resultado: ✅ CORRETO
```

### Cenário 2: Venda Total

```
Intermediação B:
  Quantidade: 5
  
Venda: Vende 5
  Remanescente: 5 - 5 = 0
  
BD Final: Quantidade = 0

Resultado: ✅ CORRETO
```

### Cenário 3: Venda Múltipla

```
Intermediação C:
  Quantidade: 20
  
User A: Vende 7 → Remanescente: 13
User B: Vende 5 → Remanescente: 8
User C: Vende 8 → Remanescente: 0
  
BD Final: Quantidade = 0

Resultado: ✅ CORRETO
```

---

## 🔒 Validações de Segurança

### Teste 1: Sem Autenticação

**Passo:**
1. Logout (se logado)
2. Acesse: `http://localhost:8000/index.php?controller=negociacao&action=painel`

**Resultado Esperado:**
- [ ] Redireciona para login
- [ ] Não acessa painel

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 2: ID Inválido

**Passo:**
1. Acesse: `http://localhost:8000/index.php?controller=negociacao&action=formulario&id=99999`

**Resultado Esperado:**
- [ ] Exibe erro: "Negociação não encontrada"
- [ ] Link para voltar disponível
- [ ] Sem crash

**Status:** [ ] Passar / [ ] Falhar

---

### Teste 3: Quantidade Negativa

**Passo:**
1. Abra formulário
2. Tente submeter com quantidade negativa (via devtools)

**Resultado Esperado:**
- [ ] Validação server-side rejeita
- [ ] Exibe erro
- [ ] BD não é alterado

**Status:** [ ] Passar / [ ] Falhar

---

## 📊 Validações de Dados

### Teste 1: Conversão de Data

```
BD:        2025-03-15
Esperado:  15/03/2025
Resultado: [✓] ou [✗]
```

### Teste 2: Conversão de Valor

```
BD:        500000 (centavos)
Esperado:  R$ 5.000,00
Resultado: [✓] ou [✗]
```

### Teste 3: Conversão de Taxa

```
DB:        575
Esperado:  5,75%
Resultado: [✓] ou [✗]
```

---

## 🎯 Resumo de Testes

| Teste | Status | Observações |
|-------|--------|-------------|
| 1. Link no Menu | [ ] | |
| 2. URL Direta | [ ] | |
| 3. Dados da Tabela | [ ] | |
| 4. Abrir Formulário | [ ] | |
| 5. Pré-preenchimento | [ ] | |
| 6. Validação Mínima | [ ] | |
| 7. Validação Máxima | [ ] | |
| 8. Cálculo Automático | [ ] | |
| 9. Venda com Sucesso | [ ] | |
| 10. Cancelar | [ ] | |
| 11. Filtro Cliente | [ ] | |
| 12. Filtro Produto | [ ] | |
| 13. Desktop | [ ] | |
| 14. Tablet | [ ] | |
| 15. Mobile | [ ] | |
| 16. Erro BD | [ ] | |

---

## 📝 Relatório de Testes

```
Data do Teste: _______________
Testador: _______________
MySQL Status: [ ] Online [ ] Offline

Testes Passados: ___ / 16
Testes Falhados: ___ / 16
Taxa de Sucesso: ___%

Problemas Encontrados:
1. _______________
2. _______________
3. _______________

Observações:
_______________
_______________
_______________
```

---

## 🔧 Troubleshooting Rápido

### "404 - Controller não encontrado"
- [ ] Verifique require em index.php
- [ ] Verifique registro em $controllers
- [ ] Verifique sintaxe de NegociacaoController.php

### "Negociação não encontrada"
- [ ] Verifique ID na URL
- [ ] Verifique se registro existe no BD
- [ ] Verifique conexão MySQL

### Dados não aparecem em DD/MM/AAAA
- [ ] Verifique formato no BD (deve ser AAAA-MM-DD)
- [ ] Teste função formatarData()
- [ ] Verifique se strtotime() funciona

### Valores não em R$
- [ ] Verifique se valores estão em centavos
- [ ] Divida por 100: R$ = valor_bd / 100
- [ ] Teste função formatarMoeda()

### Formulário não submete
- [ ] Verifique validação JavaScript
- [ ] Abra console (F12)
- [ ] Verifique erros de JavaScript
- [ ] Tente desabilitar JavaScript e testar

---

## ✅ Checklist Final

- [ ] Todos os arquivos PHP com sintaxe validada
- [ ] Painel lista negociações corretamente
- [ ] Dados convertidos (datas DD/MM/AAAA, valores em R$)
- [ ] Formulário abre com dados pré-preenchidos
- [ ] Validação de quantidade funciona
- [ ] Cálculo de remanescente automático
- [ ] Venda confirma e atualiza BD
- [ ] Mensagem de sucesso exibida
- [ ] Tabela atualizada após venda
- [ ] Filtros funcionam
- [ ] Layout responsivo
- [ ] Sem erros de 404 ou conexão
- [ ] Segurança: requer login

---

**Status Geral:** [ ] PRONTO PARA PRODUÇÃO

Quando todos os testes passarem com sucesso, a implementação estará completa e pronta para uso!

🎉 **Bom teste!**
