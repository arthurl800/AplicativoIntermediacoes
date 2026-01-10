# 🚀 Guia de Publicação - Sistema de Intermediações

## 📋 Pré-requisitos
- Arquivo Excel com dados a importar
- Conta em serviço de hospedagem gratuita

## 🆓 Opções de Hospedagem Gratuita

### Opção 1: InfinityFree (Recomendado)
**Website:** https://infinityfree.net

✅ **Vantagens:**
- Totalmente gratuito
- PHP 8.x e MySQL inclusos
- cPanel com PHPMyAdmin
- Sem anúncios obrigatórios
- SSL gratuito

📝 **Limitações:**
- 5GB de espaço
- Largura de banda ilimitada
- 400 requests/dia (limite soft)

### Opção 2: 000webhost
**Website:** https://www.000webhost.com

✅ **Vantagens:**
- Gratuito
- PHP e MySQL
- SSL gratuito

📝 **Limitações:**
- 300MB de espaço
- 3GB de largura de banda
- 1 site

---

## 🛠️ Passo a Passo - Deploy no InfinityFree

### 1️⃣ Criar Conta e Configurar Hospedagem

1. Acesse https://infinityfree.net
2. Clique em "Sign Up" e crie sua conta
3. No painel, clique em "Create Account"
4. Escolha um subdomínio gratuito (ex: `intermediacoes.epizy.com`)
5. Aguarde a ativação (2-5 minutos)

### 2️⃣ Configurar Banco de Dados

1. No cPanel, clique em "MySQL Databases"
2. Crie um novo banco de dados:
   - Nome: `epiz_xxxxx_intermediacoes` (anote o nome completo)
3. Crie um novo usuário MySQL:
   - Usuário: `epiz_xxxxx_user`
   - Senha: [crie uma senha forte]
   - Clique em "Create User"
4. Associe o usuário ao banco:
   - Selecione o usuário e o banco
   - Marque "ALL PRIVILEGES"
   - Clique em "Add"

### 3️⃣ Importar Estrutura do Banco de Dados

1. No cPanel, clique em "phpMyAdmin"
2. Selecione seu banco de dados na barra lateral
3. Clique na aba "SQL"
4. Copie e cole o conteúdo do arquivo `setup_database.sql`
5. Clique em "Go" para executar
6. Verifique se todas as tabelas foram criadas

### 4️⃣ Fazer Upload dos Arquivos

**Opção A - Via File Manager (Interface Web):**

1. No cPanel, clique em "File Manager"
2. Navegue até a pasta `htdocs`
3. **DELETE** todos os arquivos padrão (index.html, etc)
4. Clique em "Upload"
5. Faça upload de TODOS os arquivos do projeto:
   ```
   - app/
   - assets/
   - config/
   - includes/
   - vendor/
   - .htaccess
   - .env.example
   - composer.json
   - index.php
   - setup_database.sql
   ```

**Opção B - Via FTP:**

1. Use FileZilla ou outro cliente FTP
2. Credenciais FTP estão no painel da InfinityFree
3. Conecte e envie todos os arquivos para `/htdocs/`

### 5️⃣ Configurar Variáveis de Ambiente

1. No File Manager, localize o arquivo `.env.example`
2. Clique com botão direito > "Rename" > renomeie para `.env`
3. Clique com botão direito > "Edit"
4. Configure suas credenciais do banco:

```env
DB_HOST=sqlXXX.epizy.com
DB_NAME=epiz_xxxxx_intermediacoes
DB_USER=epiz_xxxxx_user
DB_PASS=sua_senha_criada
DB_CHARSET=utf8mb4

TABLE_NAME=INTERMEDIACOES_TABLE
USER_TABLE=USUARIOS_TABLE

APP_ENV=production
APP_DEBUG=false
APP_URL=https://intermediacoes.epizy.com

MEMORY_LIMIT=512M
UPLOAD_MAX_FILESIZE=50M
```

5. Salve o arquivo

### 6️⃣ Configurar .htaccess

1. Verifique se o arquivo `.htaccess` existe na raiz
2. Se não existir, crie com o seguinte conteúdo:

```apache
# Segurança - Ocultar .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# PHP Settings
php_value memory_limit 512M
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300

# Rewrite Engine
RewriteEngine On
RewriteBase /

# Redirecionar HTTP para HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger diretórios sensíveis
RewriteRule ^config/ - [F,L]
RewriteRule ^vendor/ - [F,L]
RewriteRule ^logs/ - [F,L]

# Página inicial
DirectoryIndex index.php
```

### 7️⃣ Ativar SSL (HTTPS)

1. No painel da InfinityFree, vá em "SSL Certificates"
2. Ative o SSL gratuito do CloudFlare ou Let's Encrypt
3. Aguarde a ativação (até 24h)

### 8️⃣ Primeiro Acesso

1. Acesse seu site: `https://intermediacoes.epizy.com`
2. Faça login com credenciais padrão:
   - **Usuário:** `admin`
   - **Senha:** `admin123`
3. **IMPORTANTE:** Vá em Gerenciar Usuários e ALTERE A SENHA imediatamente!

---

## 📊 Importar Seus Dados

1. Faça login no sistema
2. Vá em "Upload de Dados"
3. Selecione seu arquivo Excel
4. Clique em "Importar"
5. Aguarde o processamento

---

## 🔧 Solução de Problemas Comuns

### ❌ Erro 500 - Internal Server Error
**Causa:** Configuração incorreta do .env ou permissões
**Solução:**
```bash
# No File Manager, verifique permissões:
- Pastas: 755
- Arquivos: 644
- .env: 600 (somente leitura do servidor)
```

### ❌ Erro de Conexão com Banco de Dados
**Causa:** Credenciais incorretas no .env
**Solução:**
- Verifique DB_HOST (geralmente é `sqlXXX.epizy.com`, não `localhost`)
- Confirme DB_NAME, DB_USER e DB_PASS no painel MySQL

### ❌ Upload de Arquivo Falha
**Causa:** Limite de memória ou tamanho de arquivo
**Solução:**
- Reduza o tamanho do arquivo Excel
- Divida a importação em múltiplos arquivos menores

### ❌ Página em Branco
**Causa:** Erro PHP não exibido
**Solução:**
- Ative temporariamente no index.php:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 🔒 Segurança Pós-Deploy

### ✅ Checklist de Segurança

- [ ] Senha do admin alterada
- [ ] Arquivo .env com permissões 600
- [ ] SSL/HTTPS ativado
- [ ] APP_DEBUG=false no .env
- [ ] Backup regular do banco de dados (via phpMyAdmin)
- [ ] Atualizar senha do banco periodicamente

### 💾 Fazer Backup

**Banco de Dados:**
1. phpMyAdmin > Selecione banco > Export
2. Formato: SQL
3. Salve o arquivo

**Arquivos:**
1. File Manager > Selecione htdocs
2. Compress > Download ZIP

---

## 📱 Acesso Móvel

O sistema é responsivo e funciona em:
- ✅ Desktop
- ✅ Tablet
- ✅ Smartphone

---

## 🆘 Suporte

Se encontrar problemas:

1. Verifique os logs em: `File Manager > logs/`
2. Ative debug temporariamente no .env: `APP_DEBUG=true`
3. Consulte documentação da InfinityFree

---

## 🎉 Pronto!

Seu sistema está no ar em: **https://intermediacoes.epizy.com**

**Próximos passos:**
- Importar seus dados
- Criar usuários adicionais
- Começar a registrar negociações
- Consultar relatórios e auditorias
