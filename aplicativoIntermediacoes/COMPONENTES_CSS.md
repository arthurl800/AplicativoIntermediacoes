# 🎨 Guia de Componentes CSS - Tema Moderno

## Tema Verde e Dourado

Este documento descreve todos os componentes CSS disponíveis para manter a consistência visual em toda a aplicação.

### 📋 Cores Principais

```css
--primary-dark: #1b5e20 (Verde escuro)
--primary: #2e7d32 (Verde principal)
--primary-light: #4caf50 (Verde claro)
--secondary: #fbc02d (Dourado)
--secondary-dark: #f9a825 (Dourado escuro)
```

---

## 🔘 Botões

### Botão Primário (Verde)
```html
<a href="#" class="btn btn-primary">Salvar</a>
<button class="btn btn-primary">Confirmar</button>
```

### Botão Secundário (Dourado)
```html
<a href="#" class="btn btn-secondary">Exportar</a>
```

### Botão Sucesso (Azul)
```html
<button class="btn btn-success">Sucesso</button>
```

### Botão Perigo (Vermelho)
```html
<button class="btn btn-danger">Deletar</button>
```

### Botão Outline
```html
<button class="btn btn-outline">Cancelar</button>
```

### Botão Pequeno
```html
<a href="#" class="btn btn-small btn-primary">Editar</a>
```

### Botão em Bloco (Full Width)
```html
<button class="btn btn-primary btn-block">Enviar</button>
```

---

## 📦 Cards

### Card Padrão
```html
<div class="card">
    <div class="card-header">
        <h2>Título do Card</h2>
    </div>
    <div class="card-body">
        Conteúdo aqui...
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Ação</button>
    </div>
</div>
```

### Card KPI (Indicador)
```html
<div class="card-kpi">
    <h3>Total de Negociações</h3>
    <p class="value">1,234</p>
</div>
```

---

## 📊 Tabelas

```html
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dados 1</td>
                <td>Dados 2</td>
                <td>
                    <a href="#" class="btn btn-small btn-primary">Editar</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

## 🏷️ Badges (Etiquetas)

### Sucesso (Verde)
```html
<span class="badge badge-success">Aprovado</span>
```

### Aviso (Amarelo)
```html
<span class="badge badge-warning">Pendente</span>
```

### Perigo (Vermelho)
```html
<span class="badge badge-danger">Rejeitado</span>
```

### Info (Azul)
```html
<span class="badge badge-info">Informação</span>
```

### Auditoria
```html
<span class="badge badge-insert">INSERT</span>
<span class="badge badge-update">UPDATE</span>
<span class="badge badge-delete">DELETE</span>
```

---

## ⚠️ Alerts (Avisos)

### Alert Sucesso
```html
<div class="alert alert-success">
    ✅ Operação realizada com sucesso!
</div>
```

### Alert Aviso
```html
<div class="alert alert-warning">
    ⚠️ Atenção: Dados alterados
</div>
```

### Alert Perigo
```html
<div class="alert alert-danger">
    ❌ Erro: Não foi possível salvar
</div>
```

### Alert Info
```html
<div class="alert alert-info">
    ℹ️ Informação: Confira os dados
</div>
```

---

## 📋 Formulários

```html
<form>
    <div class="form-group">
        <label for="input1">Campo de Texto</label>
        <input type="text" id="input1" placeholder="Digite aqui...">
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="seu@email.com">
    </div>
    
    <div class="form-group">
        <label for="select1">Selecione</label>
        <select id="select1">
            <option>Opção 1</option>
            <option>Opção 2</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="textarea1">Descrição</label>
        <textarea id="textarea1" placeholder="Digite..."></textarea>
    </div>
    
    <button class="btn btn-primary btn-block">Enviar</button>
</form>
```

---

## 🎨 Grids e Layouts

### Grid 2 Colunas
```html
<div class="grid grid-2">
    <div class="card">Coluna 1</div>
    <div class="card">Coluna 2</div>
</div>
```

### Grid 3 Colunas
```html
<div class="grid grid-3">
    <div class="card">Coluna 1</div>
    <div class="card">Coluna 2</div>
    <div class="card">Coluna 3</div>
</div>
```

### Grid 4 Colunas
```html
<div class="grid grid-4">
    <div class="card">1</div>
    <div class="card">2</div>
    <div class="card">3</div>
    <div class="card">4</div>
</div>
```

### Flex (Flexbox)
```html
<div class="flex">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>

<!-- Flex com espaçamento -->
<div class="flex flex-between">
    <h2>Título</h2>
    <button class="btn btn-primary">Ação</button>
</div>

<!-- Flex em coluna -->
<div class="flex flex-column">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

---

## 🔢 Paginação

```html
<div class="pagination">
    <a href="?page=1">← Anterior</a>
    <span>Página 2 de 5</span>
    <a href="?page=3">Próxima →</a>
</div>
```

---

## 🎯 Utilitários

### Texto
```html
<p class="text-center">Texto centralizado</p>
<p class="text-right">Texto à direita</p>
<p class="text-primary">Texto em verde</p>
<p class="text-secondary">Texto em dourado</p>
<p class="text-danger">Texto em vermelho</p>
<p class="text-muted">Texto neutro</p>
```

### Margem e Padding
```html
<!-- Margem Bottom -->
<div class="mb-1">Margem pequena</div>
<div class="mb-2">Margem média</div>
<div class="mb-3">Margem grande</div>
<div class="mb-4">Margem muito grande</div>

<!-- Margem Top -->
<div class="mt-1">Margem top pequena</div>

<!-- Padding -->
<div class="p-1">Espaçamento pequeno</div>
<div class="p-2">Espaçamento médio</div>
<div class="p-3">Espaçamento grande</div>
```

### Visibilidade
```html
<div class="hidden">Oculto</div>
<div class="visible">Visível</div>
```

---

## 📱 Responsividade

O tema é totalmente responsivo com breakpoints em:
- **1280px+**: Desktop
- **768px-1279px**: Tablet
- **< 768px**: Mobile

Todos os componentes se adaptam automaticamente!

---

## 💡 Exemplos Completos

### Página de Dashboard
```html
<h1 class="mb-3">Dashboard</h1>

<div class="grid grid-4">
    <div class="card-kpi">
        <h3>Total</h3>
        <p class="value">1.234</p>
    </div>
    <div class="card-kpi">
        <h3>Valor</h3>
        <p class="value">R$ 5M</p>
    </div>
    <div class="card-kpi">
        <h3>Clientes</h3>
        <p class="value">45</p>
    </div>
    <div class="card-kpi">
        <h3>Taxa Média</h3>
        <p class="value">2.5%</p>
    </div>
</div>
```

### Formulário
```html
<div class="card">
    <div class="card-header">
        <h2>Adicionar Negociação</h2>
    </div>
    <div class="card-body">
        <form>
            <div class="grid grid-2">
                <div class="form-group">
                    <label>Cliente</label>
                    <input type="text" placeholder="Nome do cliente">
                </div>
                <div class="form-group">
                    <label>Produto</label>
                    <select>
                        <option>LCA</option>
                        <option>CDB</option>
                        <option>RDB</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade</label>
                    <input type="number" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Valor</label>
                    <input type="text" placeholder="R$ 0,00">
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Salvar</button>
        <button class="btn btn-outline">Cancelar</button>
    </div>
</div>
```

---

## 📚 Variáveis CSS Disponíveis

```css
/* Cores */
--primary-dark, --primary, --primary-light, --primary-lighter, --primary-lightest
--secondary, --secondary-dark, --secondary-light
--bg-dark, --bg-light, --bg-white
--text-dark, --text-light
--border-light, --border-medium

/* Sombras */
--shadow-sm, --shadow-md, --shadow-lg

/* Espaçamento */
--spacing-xs, --spacing-sm, --spacing-md, --spacing-lg, --spacing-xl, --spacing-2xl

/* Border Radius */
--radius-sm, --radius-md, --radius-lg

/* Transições */
--transition-fast, --transition-normal, --transition-slow
```

Use essas variáveis em seus componentes customizados:
```css
.meu-componente {
    background-color: var(--primary);
    padding: var(--spacing-md);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}
```

---

**Versão**: 1.0  
**Última atualização**: Dezembro 2025  
**Tema**: Verde e Dourado - Moderno e Responsivo
