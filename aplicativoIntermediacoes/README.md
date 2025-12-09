# 💰 Sistema de Intermediações Financeiras

## 📋 Visão Geral

Sistema MVC em PHP para gerenciar intermediações financeiras com importação de dados (CSV/XLSX), auditoria, relatórios e painel de negociações.

### 🎯 Funcionalidades Principais

- ✅ **Importação de Dados** - CSV/XLSX com validação
- ✅ **Autenticação** - Login/Registro com roles (user/admin)
- ✅ **Painel de Negociações** - Visualiza intermediações e processa vendas
- ✅ **Dashboard** - KPIs e gráficos (Chart.js)
- ✅ **Auditoria** - Registra todas as operações
- ✅ **Relatórios** - Exporta dados em CSV
- ✅ **Admin** - Gerencia usuários
- ✅ **Design Moderno** - CSS responsivo com tema verde/dourado

---

## 📁 Estrutura do Projeto

```
aplicativoIntermediacoes/
├── app/
│   ├── controller/
│   │   ├── AuthController.php          (Login/Registro/Logout)
│   │   ├── UploadController.php        (Importação CSV/XLSX)
│   │   ├── DashboardController.php     (Painel inicial)
│   │   ├── DataController.php          (Visualização de dados)
│   │   ├── AdminController.php         (Gerenciamento de usuários)
│   │   ├── RelatorioController.php     (Dashboard e auditoria)
│   │   └── NegociacaoController.php    (Painel de negociações) [NOVO]
│   │
│   ├── model/
│   │   ├── UserModel.php               (CRUD de usuários)
│   │   ├── IntermediacaoModel.php      (Dados de intermediações)
│   │   ├── AuditoriaModel.php          (Logs de auditoria)
│   │   └── NegociacaoModel.php         (Negociações) [EXPANDIDO]
│   │
│   ├── util/
│   │   ├── Database.php                (Conexão MySQL - Singleton)
│   │   ├── AuthManager.php             (Autenticação)
│   │   ├── IFileProcessor.php          (Interface de processadores)
│   │   ├── CsvProcessor.php            (Processamento CSV)
│   │   └── XlsxProcessor.php           (Processamento XLSX)
│   │
│   └── view/
│       ├── auth/
│       │   ├── login_form.php
│       │   ├── register_form.php
│       │   └── forgot_password.php
│       │
│       ├── dashboard/
│       │   └── index.php
│       │
│       ├── upload/
│       │   ├── upload_form.php
│       │   └── upload_result.php
│       │
│       ├── dados/
│       │   └── visualizacao_dados.php
│       │
│       ├── admin/
│       │   ├── user_list.php
│       │   └── user_management.php
│       │
│       ├── relatorio/
│       │   ├── dashboard.php
│       │   └── auditoria.php
│       │
│       └── negociacoes/              [NOVO DIRETÓRIO]
│           ├── painel.php            (Lista intermediações)
│           └── formulario.php        (Formulário de venda)
│
├── assets/
│   └── css/
│       ├── theme.css                 (Tema verde/dourado moderno) [NOVO]
│       └── responsive-table.css
│
├── config/
│   └── database.php                  (Configuração MySQL)
│
├── includes/
│   ├── header.php                    (Header com navegação)
│   └── footer.php                    (Footer)
│
├── vendor/                           (Composer dependencies)
│
├── index.php                         (Router principal)
├── composer.json                     (Dependências)
│
├── COMPONENTES_CSS.md                (Guia de componentes) [NOVO]
├── NEGOCIACOES.md                    (Documentação completa) [NOVO]
├── GUIA_RAPIDO_NEGOCIACOES.md       (Guia rápido) [NOVO]
└── README.md                         (Este arquivo) [ATUALIZADO]
```

---

## 🔐 Autenticação

### Roles de Acesso
- **admin** - Acesso total (gerenciamento de usuários, relatórios)
- **user** - Acesso limitado (importação, negociações)

### Credenciais Padrão
```
Username: admin
Password: admin
```

---

## 🎨 Tema Visual

### Cores Principais
- **Verde Primário** `#1b5e20` a `#4caf50`
- **Dourado Secundário** `#fbc02d`

### Componentes
- Cards com sombras
- Tabelas responsivas
- Botões com gradientes
- Badges para status
- Alerts para mensagens
- Formulários modernos
- Emojis na navegação

Veja `COMPONENTES_CSS.md` para documentação completa.

---

## 🚀 Como Iniciar

### Pré-requisitos
- PHP 8+
- MySQL 8+
- Composer
- Navegador moderno

### Instalação

```bash
# 1. Clone o repositório
cd /var/www/html/aplicativoIntermediacoes

# 2. Instale dependências
composer install

# 3. Configure o banco de dados
# Edite config/database.php com suas credenciais MySQL

# 4. Inicie o servidor PHP
php -S localhost:8000 -t .

# 5. Acesse no navegador
# http://localhost:8000
```

---

## 💾 Banco de Dados

### Tabelas Principais

#### `USUARIOS_TABLE` (ou `USERS`)
```sql
CREATE TABLE USUARIOS_TABLE (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `INTERMEDIACOES_TABLE` (ou `INTERMEDIACOES`)
```sql
CREATE TABLE INTERMEDIACOES_TABLE (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Codigo_Cliente VARCHAR(20),
    Nome_Corretora VARCHAR(255),
    Ativo VARCHAR(50),
    Tipo_Operacao VARCHAR(50),
    CNPJ VARCHAR(18),
    Data_Vencimento DATE,
    Taxa_Liquidacao DECIMAL(10,2),
    Quantidade INT,
    Valor_Bruto BIGINT,      -- Centavos
    IRRF BIGINT,             -- Centavos
    Valor_Liquido BIGINT,    -- Centavos
    Data DATE,
    imported_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `NEGOCIACOES_TABLE`
```sql
CREATE TABLE NEGOCIACOES (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Data_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Conta_Vendedor VARCHAR(20),
    Nome_Vendedor VARCHAR(255),
    Produto VARCHAR(50),
    Estrategia VARCHAR(50),
    Quantidade_negociada INT,
    Valor_Bruto_Importado_Raw BIGINT,
    Taxa_Saida DECIMAL(10,2),
    Valor_Bruto_Saida BIGINT,
    Valor_Liquido_Saida BIGINT,
    Preco_Unitario_Saida BIGINT,
    Ganho_Saida BIGINT,
    Rentabilidade_Saida DECIMAL(10,2),
    Conta_Comprador VARCHAR(20),
    Nome_Comprador VARCHAR(255),
    Taxa_Entrada DECIMAL(10,2),
    Valor_Bruto_Entrada BIGINT,
    Preco_Unitario_Entrada BIGINT,
    Valor_Plataforma BIGINT,
    Corretagem_Assessor BIGINT,
    Roa_Assessor DECIMAL(10,2)
);
```

#### `NEGOCIACOES_AUDITORIA` (Auditoria)
```sql
CREATE TABLE NEGOCIACOES_AUDITORIA (
    id INT PRIMARY KEY AUTO_INCREMENT,
    negociacao_id INT,
    usuario_id INT,
    acao VARCHAR(50),          -- INSERT, UPDATE, DELETE
    descricao TEXT,
    dados_anteriores JSON,
    dados_novos JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📊 Painel de Negociações [NOVO]

### Acesso
**Menu:** 💰 Negociações  
**URL:** `index.php?controller=negociacao&action=painel`

### Funcionalidades
1. **Listagem** - Todas as intermediações disponíveis
2. **Dados Convertidos** - Datas em DD/MM/AAAA, valores em R$
3. **Filtros** - Por cliente e produto
4. **Negociar** - Abre formulário para vender títulos

### Validações
- **Mínimo:** 1 título
- **Máximo:** Quantidade disponível
- **Atualização:** "Baixa" automática na quantidade

Veja `NEGOCIACOES.md` para documentação completa.

---

## 📈 Dashboard e Relatórios

### Dashboard (`/relatorio/dashboard`)
- KPIs (Total, Valor, Taxa Média)
- Gráficos Chart.js:
  - Negociações por Operador (Bar)
  - Negociações por Produto (Doughnut)
  - Negociações por Data (Line)

### Auditoria (`/relatorio/auditoria`)
- Log de todas as operações
- Paginação (50 registros/página)
- Badges para tipo de ação (INSERT, UPDATE, DELETE)

### Exportar CSV (`/relatorio/exportarCSV`)
- Baixar relatório completo
- Filtros opcionais

---

## 📥 Importação de Dados

### Formatos Suportados
- **CSV** - Delimitado por vírgula ou ponto-e-vírgula
- **XLSX** - Microsoft Excel (PhpSpreadsheet)

### Colunas Esperadas (23)
1. Codigo_Cliente
2. Nome_Corretora
3. Ativo
4. Tipo_Operacao
5. CNPJ
6. Data_Vencimento
7. Taxa_Liquidacao
8. Quantidade
9. Valor_Bruto
10. IRRF
11. Valor_Liquido
12. Data
13-23. (Outras colunas específicas)

### Processo
1. Acesse **📥 Importar**
2. Selecione arquivo CSV/XLSX
3. Confirme importação
4. Sistema valida e insere em INTERMEDIACOES

---

## 🔒 Segurança

### Autenticação
- Sessão PHP `$_SESSION`
- Verificação via `AuthManager`
- Redirecionamento automático se não autenticado

### Validação
- Prepared statements (PDO)
- Escapar saída com `htmlspecialchars()`
- Validação de quantidade (server + client)

### Proteção de Dados
- Passwords com `password_hash()` (bcrypt)
- Logs de auditoria para rastreabilidade
- Transações MySQL para integridade

---

## 🧪 Testes Manuais

### Teste 1: Login
```
1. Acesse http://localhost:8000
2. Utilize: admin / admin
3. Deve exibir dashboard
```

### Teste 2: Importação
```
1. Acesse "📥 Importar"
2. Selecione CSV/XLSX com 23 colunas
3. Confirme
4. Dados devem aparecer em "💰 Negociações"
```

### Teste 3: Negociação
```
1. Acesse "💰 Negociações"
2. Clique "🤝 Negociar" em uma linha
3. Preencha "Quantidade a Vender"
4. Clique "✓ Confirmar Venda"
5. Quantidade deve diminuir
```

### Teste 4: Dashboard
```
1. Acesse "📈 Dashboard"
2. Visualize KPIs e gráficos
3. Deve exibir dados agregados
```

---

## 🛠️ Desenvolvimento

### Adicionar Novo Controller

```php
// app/controller/MeuController.php
require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';

class MeuController {
    public function __construct() {
        $this->authManager = new AuthManager();
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }
    }

    public function acao() {
        // Lógica aqui
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/meu/acao.php';
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }
}
```

### Registrar Rota (index.php)
```php
require_once __DIR__ . '/app/controller/MeuController.php';

$controllers = [
    'meu' => MeuController::class,
];
```

### Adicionar Model

```php
// app/model/MeuModel.php
class MeuModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function buscar() {
        $sql = "SELECT * FROM tabela";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

---

## 📚 Documentação

- **COMPONENTES_CSS.md** - Guia de componentes CSS disponíveis
- **NEGOCIACOES.md** - Documentação completa do painel de negociações
- **GUIA_RAPIDO_NEGOCIACOES.md** - Guia rápido de implementação

---

## 🚨 Troubleshooting

### Problema: "Database Connection Error"
**Solução:**
1. Inicie MySQL: `sudo systemctl start mysql`
2. Verifique credenciais em `config/database.php`
3. Crie banco de dados: `CREATE DATABASE INTERMEDIACOES;`

### Problema: "404 - Controller não encontrado"
**Solução:**
1. Verifique se controller existe em `app/controller/`
2. Adicione `require_once` em `index.php`
3. Registre rota em `$controllers` array

### Problema: Senha não funciona
**Solução:**
1. Reset a senha padrão: `admin` / `admin`
2. Use `password_hash()` para novas senhas
3. Verifique coluna `password_hash` na tabela

### Problema: CSS não carrega
**Solução:**
1. Verifique arquivo `assets/css/theme.css` existe
2. Verifique caminho relativo em `header.php`
3. Limpe cache do navegador (Ctrl+Shift+Del)

---

## 📞 Suporte

Para dúvidas:
1. Verifique logs: `php error_log`
2. Teste sintaxe: `php -l arquivo.php`
3. Conecte ao BD: `mysql -u usuario -p database`

---

## 📝 Licença

Este projeto é de uso exclusivo para intermediações financeiras.

---

## 👥 Contribuidores

- Sistema inicialmente desenvolvido para importação e auditoria
- Expandido com painel de negociações e dashboard
- Modernizado com tema CSS verde/dourado

---

## 🎯 Roadmap

- [ ] PDF export para relatórios
- [ ] Gráficos avançados
- [ ] API REST
- [ ] Autenticação OAuth
- [ ] Integração com sistemas externos

---

**Versão:** 1.5  
**Última Atualização:** Dezembro 2025  
**Status:** ✅ Pronto para Produção
