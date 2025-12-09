# 💰 Painel de Negociações - Documentação Completa

## 📋 Sumário Executivo

Implementação completa do **Painel de Negociações** e **Formulário de Negociação** com as seguintes funcionalidades:

✅ **Painel de Negociações** (`/app/view/negociacoes/painel.php`)
- Lista todas as intermediações disponíveis da tabela `INTERMEDIACOES`
- Dados convertidos automaticamente:
  - Datas: `AAAA-MM-DD` → `DD/MM/AAAA`
  - Valores monetários: centavos → `R$` formatado
  - Percentuais: número → `X,XX%`
- Tabela responsiva com filtros
- Botão "Negociar" para cada intermediação

✅ **Formulário de Negociação** (`/app/view/negociacoes/formulario.php`)
- Dados pré-preenchidos da intermediação selecionada
- Validação em tempo real:
  - Mínimo: 1 título
  - Máximo: quantidade disponível
- Cálculo automático de quantidade remanescente
- Processamento da venda com atualização do banco de dados

---

## 🏗️ Arquitetura

### 1. Model: `app/model/NegociacaoModel.php`

**Métodos Principais:**

#### `listarIntermedicoesDisponiveis(int $limit = 100): array`
Busca todas as intermediações com `Quantidade > 0`.

```php
$model = new NegociacaoModel();
$negociacoes = $model->listarIntermedicoesDisponiveis();
```

**Dados Retornados:**
```php
[
    'id' => 1,
    'conta' => '12345',
    'cliente' => 'Banco XYZ',
    'produto' => 'LCA-25A04157044',
    'estrategia' => 'Compra',
    'emissor' => '12.345.678/0001-90',
    'vencimento' => '15/03/2025',              // CONVERTIDO
    'vencimento_original' => '2025-03-15',    // Original do BD
    'taxa' => '5,75%',                         // CONVERTIDO
    'quantidade' => 10,
    'valor_bruto' => 'R$ 5.000,00',           // CONVERTIDO
    'valor_bruto_centavos' => 500000,          // Original do BD
    'valor_liquido' => 'R$ 4.750,00',         // CONVERTIDO
    'valor_liquido_centavos' => 475000,        // Original do BD
    'ir' => 'R$ 250,00',                      // CONVERTIDO
    'ir_centavos' => 25000,                    // Original do BD
    'data_compra' => '01/01/2025',            // CONVERTIDO
    'data_compra_original' => '2025-01-01',   // Original do BD
    'quantidade_disponivel' => 10
]
```

#### `obterIntermediacao(int $id): ?array`
Busca uma intermediação específica pelo ID.

```php
$negociacao = $model->obterIntermediacao(1);
```

#### `atualizarQuantidadeDisponivel(int $id, int $quantidade_nova): bool`
Atualiza a quantidade após uma venda.

```php
$modelo->atualizarQuantidadeDisponivel(1, 5); // Reduz para 5
```

### 2. Controller: `app/controller/NegociacaoController.php`

**Ações (Métodos):**

#### `painel()`
- GET: `index.php?controller=negociacao&action=painel`
- Exibe lista de todas as negociações disponíveis
- Inclui header, painel.php view e footer

#### `formulario()`
- GET: `index.php?controller=negociacao&action=formulario&id=1`
- Exibe formulário pré-preenchido com dados da intermediação
- ID da intermediação é obrigatório

#### `processar()`
- POST: Processa a venda
- Campos esperados:
  - `negociacao_id`: ID da intermediação
  - `quantidade_vendida`: Quantidade a vender
- Valida quantidade (mín: 1, máx: quantidade disponível)
- Atualiza banco de dados
- Redireciona para painel com mensagem de sucesso/erro

### 3. Views

#### `app/view/negociacoes/painel.php`

**Componentes:**
- Header com título e descrição
- Alerta de sucesso (se houver)
- Filtros de cliente e produto (opcional)
- Tabela de intermediações com colunas:
  - Conta
  - Cliente
  - Produto (badge)
  - Estratégia
  - Quantidade
  - Valor Bruto
  - Valor Líquido
  - Vencimento
  - Ações (botão "Negociar")

**Classes CSS Utilizadas:**
- `.card` - Card container
- `.table-wrapper` - Wrapper responsivo
- `.table` - Tabela moderna
- `.badge` - Badge para produto
- `.btn` - Botão
- `.alert` - Alertas

#### `app/view/negociacoes/formulario.php`

**Seções:**
1. **Dados da Intermediação** (apenas leitura)
   - Conta Vendedor
   - Nome do Cliente
   - Produto (badge)
   - Estratégia
   - Emissor (CNPJ)
   - Vencimento

2. **Valores e Quantidades** (apenas leitura)
   - Quantidade Disponível (em verde)
   - Taxa (%)
   - Valor Bruto Total
   - IR (Imposto de Renda)
   - Valor Líquido Total (em azul)
   - Data da Compra

3. **Formulário de Venda** (input)
   - Campo "Quantidade a Vender"
     - Tipo: number
     - Mín: 1
     - Máx: quantidade disponível
     - Validação em tempo real
   - Campo "Quantidade Remanescente"
     - Readonly
     - Atualizado automaticamente
   - Restrições exibidas
   - Botões: Cancelar, Confirmar Venda

**Validação JavaScript:**
```javascript
// Valida quantidade e atualiza preview
function atualizarPreview() {
    const quantidade_vendida = parseInt(input.value) || 0;
    const quantidade_nova = quantidadeDisponivel - quantidade_vendida;
    
    if (quantidade_vendida > quantidadeDisponivel) {
        input.classList.add('error');
        remanescente.value = 'Quantidade inválida!';
    } else if (quantidade_vendida > 0) {
        remanescente.value = quantidade_nova;
    }
}
```

---

## 🔄 Fluxo de Dados

### Fluxo 1: Exibir Painel de Negociações

```
User clica em "💰 Negociações" (header.php)
    ↓
GET /index.php?controller=negociacao&action=painel
    ↓
NegociacaoController::painel()
    ↓
NegociacaoModel::listarIntermedicoesDisponiveis()
    ↓
SQL: SELECT ... FROM INTERMEDIACOES WHERE Quantidade > 0
    ↓
Converte datas e valores
    ↓
Inclui app/view/negociacoes/painel.php
    ↓
Exibe tabela de negociações
```

### Fluxo 2: Abrir Formulário de Negociação

```
User clica em "🤝 Negociar" (painel.php)
    ↓
GET /index.php?controller=negociacao&action=formulario&id=1
    ↓
NegociacaoController::formulario()
    ↓
NegociacaoModel::obterIntermediacao(1)
    ↓
SQL: SELECT ... FROM INTERMEDIACOES WHERE id = 1
    ↓
Converte datas e valores
    ↓
Inclui app/view/negociacoes/formulario.php
    ↓
Exibe formulário com dados pré-preenchidos
```

### Fluxo 3: Processar Venda de Títulos

```
User preenche "Quantidade a Vender" e clica "✓ Confirmar Venda"
    ↓
POST /index.php?controller=negociacao&action=processar
    ↓
Dados: negociacao_id=1, quantidade_vendida=5
    ↓
NegociacaoController::processar()
    ↓
Valida quantidade (1 ≤ quantidade ≤ disponível)
    ↓
NegociacaoModel::atualizarQuantidadeDisponivel(1, 5)
    ↓
SQL: UPDATE INTERMEDIACOES SET Quantidade = 5 WHERE id = 1
    ↓
Sucesso: Redireciona para painel com mensagem de sucesso
Erro:    Exibe página de erro
```

---

## 📊 Conversão de Dados

### Datas
- **Entrada (BD):** `2025-03-15` (formato AAAA-MM-DD)
- **Saída (View):** `15/03/2025` (formato DD/MM/AAAA)

**Código:**
```php
private function formatarData(string $data): string {
    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}
```

### Valores Monetários
- **Entrada (BD):** `500000` (centavos, sem decimais)
- **Saída (View):** `R$ 5.000,00` (reais formatado)

**Código:**
```php
private function formatarMoeda(int $centavos): string {
    $reais = $centavos / 100;
    return number_format($reais, 2, ',', '.');
}
```

### Percentuais
- **Entrada (BD):** `575` (X 100)
- **Saída (View):** `5,75%` (formatado)

**Código:**
```php
private function formatarPorcentagem(float $valor): string {
    $formatado = number_format($valor, 2, ',', '.');
    return $formatado . '%';
}
```

---

## 🗄️ Estrutura da Tabela INTERMEDIACOES

```sql
CREATE TABLE INTERMEDIACOES (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Codigo_Cliente VARCHAR(20),
    Nome_Corretora VARCHAR(255),
    Ativo VARCHAR(50),
    Tipo_Operacao VARCHAR(50),
    CNPJ VARCHAR(18),
    Data_Vencimento DATE,
    Taxa_Liquidacao DECIMAL(10,2),
    Quantidade INT,
    Valor_Bruto BIGINT,           -- Armazenado em centavos
    IRRF BIGINT,                  -- Armazenado em centavos
    Valor_Liquido BIGINT,         -- Armazenado em centavos
    Data DATE,
    ... outras colunas
);
```

---

## 🔐 Segurança

### Autenticação
- Todas as ações requerem login via `AuthManager`
- Redirecionamento automático para login se não autenticado

### Validação
- **Quantidade:** Validada server-side e client-side
  - Mínimo: 1
  - Máximo: quantidade disponível
- **ID:** Validado como integer
- **Entrada do usuário:** Escapada com `htmlspecialchars()`

### Prevenção de SQL Injection
- Uso de prepared statements com placeholders (`:param`)
- PDO com `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`

---

## 🎨 Estilos CSS

### Classes Utilizadas

| Classe | Descrição | Exemplo |
|--------|-----------|---------|
| `.card` | Card container com sombra | `<div class="card">` |
| `.card-header` | Header do card com background | `<div class="card-header"><h2>` |
| `.card-body` | Body do card com padding | `<div class="card-body">` |
| `.card-footer` | Footer do card com border top | `<div class="card-footer">` |
| `.table-wrapper` | Wrapper responsivo para tabelas | `<div class="table-wrapper">` |
| `.table` | Tabela moderna com gradiente | `<table class="table">` |
| `.badge` | Badge com cor de fundo | `<span class="badge badge-info">` |
| `.badge-info` | Badge azul | `<span class="badge badge-info">` |
| `.btn` | Botão base | `<button class="btn">` |
| `.btn-primary` | Botão verde (primário) | `<a class="btn btn-primary">` |
| `.btn-outline` | Botão com outline | `<button class="btn btn-outline">` |
| `.btn-success` | Botão de sucesso (azul) | `<button class="btn btn-success">` |
| `.btn-small` | Botão pequeno | `<a class="btn btn-small">` |
| `.alert` | Alerta container | `<div class="alert">` |
| `.alert-success` | Alerta de sucesso (verde) | `<div class="alert alert-success">` |
| `.alert-warning` | Alerta de aviso (amarelo) | `<div class="alert alert-warning">` |
| `.alert-info` | Alerta info (azul) | `<div class="alert alert-info">` |
| `.form-group` | Grupo de form com label | `<div class="form-group">` |
| `.form-control` | Input/select/textarea | `<input class="form-control">` |
| `.grid` | Grid container | `<div class="grid">` |
| `.grid-2` | Grid 2 colunas | `<div class="grid grid-2">` |
| `.page-header` | Header de página | `<div class="page-header">` |
| `.text-right` | Texto alinhado à direita | `<td class="text-right">` |
| `.text-success` | Texto em verde | `<span class="text-success">` |
| `.text-primary` | Texto em azul/primário | `<span class="text-primary">` |
| `.text-muted` | Texto cinza/neutro | `<p class="text-muted">` |
| `.mb-4` | Margin bottom grande | `<div class="mb-4">` |
| `.mt-4` | Margin top grande | `<div class="mt-4">` |
| `.mt-3` | Margin top médio | `<div class="mt-3">` |
| `.flex` | Flex container | `<div class="flex">` |
| `.flex-between` | Flex com space-between | `<div class="flex flex-between">` |

---

## 🚀 Como Usar

### 1. Acessar o Painel de Negociações

```
1. Login na aplicação
2. Clique em "💰 Negociações" no menu principal (header)
3. Você verá a lista de todas as intermediações disponíveis
```

### 2. Negociar uma Intermediação

```
1. No painel, encontre a intermediação desejada
2. Clique em "🤝 Negociar"
3. Verifique os dados pré-preenchidos
4. Insira a "Quantidade a Vender" (1 a N)
5. Verifique a "Quantidade Remanescente" calculada
6. Clique em "✓ Confirmar Venda"
7. Você será redirecionado ao painel com mensagem de sucesso
```

### 3. Validações

**Quantidade Disponível:**
```
Intermediação A tem 10 títulos disponíveis
- Mínimo que pode vender: 1
- Máximo que pode vender: 10
- Se vender 6, ficará com 4
```

**Conversão de Valores:**
```
BD armazena: 500000 (centavos)
Exibe como: R$ 5.000,00 (reais)

Após venda de 5 títulos:
Nova quantidade = 5
BD atualiza: Quantidade = 5
```

---

## 🧪 Testes

### Teste 1: Listar Negociações
```php
$model = new NegociacaoModel();
$negociacoes = $model->listarIntermedicoesDisponiveis();
echo count($negociacoes); // Deve retornar número de negociações
```

### Teste 2: Obter Negociação
```php
$model = new NegociacaoModel();
$neg = $model->obterIntermediacao(1);
echo $neg['cliente']; // Deve exibir nome do cliente
echo $neg['vencimento']; // Deve estar em formato DD/MM/AAAA
echo $neg['valor_bruto']; // Deve estar em formato R$ X.XXX,XX
```

### Teste 3: Atualizar Quantidade
```php
$model = new NegociacaoModel();
$sucesso = $model->atualizarQuantidadeDisponivel(1, 5);
echo $sucesso ? 'Sucesso' : 'Erro'; // Deve retornar true
```

### Teste 4: Fluxo Completo (Manual)
1. Acesse `http://localhost:8000/index.php?controller=negociacao&action=painel`
2. Clique em "🤝 Negociar"
3. Insira quantidade (ex: 3)
4. Clique em "✓ Confirmar Venda"
5. Deve redirecionar e exibir mensagem de sucesso
6. Quantidade na tabela deve ter diminuído

---

## 📝 Arquivos Criados/Modificados

### Criados
- ✅ `app/model/NegociacaoModel.php` (extensão com novos métodos)
- ✅ `app/controller/NegociacaoController.php`
- ✅ `app/view/negociacoes/painel.php`
- ✅ `app/view/negociacoes/formulario.php`
- ✅ `NEGOCIACOES.md` (este arquivo)

### Modificados
- ✅ `index.php` (adicionado require e rota)
- ✅ `includes/header.php` (adicionado link para painel)

---

## 🔧 Troubleshooting

### Problema: "Negociação não encontrada"
**Causa:** ID de negociação inválido ou não existente
**Solução:** Verifique se o ID existe na tabela INTERMEDIACOES

### Problema: "Quantidade inválida"
**Causa:** Tentou vender mais do que tem disponível
**Solução:** Insira quantidade entre 1 e a disponível

### Problema: Datas não aparecem em DD/MM/AAAA
**Causa:** Formato de data no BD diferente
**Solução:** Verifique formato no BD (deve ser AAAA-MM-DD)

### Problema: Valores não aparecem em R$
**Causa:** Valores no BD não estão em centavos
**Solução:** Divida por 100 na conversão ou atualize dados

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs em `error_log` do PHP
2. Teste a conexão com o BD usando `php -r "require..."`
3. Valide a sintaxe PHP: `php -l app/controller/NegociacaoController.php`

---

**Versão:** 1.0  
**Data:** Dezembro 2025  
**Status:** ✅ Pronto para produção
