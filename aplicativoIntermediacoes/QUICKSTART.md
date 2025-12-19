# Quick Start Guide

## Local Development (5 minutos)

```bash
# 1. Clone
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
cd AplicativoIntermediacoes

# 2. Variáveis de ambiente
cp .env.example .env

# 3. Iniciar
docker compose up --build

# 4. Acessar
# Browser: http://localhost:8000
# Login: admin / admin123
```

## Railway.com Deployment (15 minutos) ⚡

### Pré-requisitos
- Conta no [Railway.app](https://railway.app)
- Repositório GitHub conectado
- Este projeto commitado no GitHub

### Passo 1: Novo Projeto no Railway

```bash
# Via Dashboard Web:
1. Acesse https://railway.app e faça login com GitHub
2. Clique em "New Project"
3. Selecione "Deploy from GitHub repo"
4. Escolha: arthurl800/AplicativoIntermediacoes
5. Railway criará o serviço (pode falhar no primeiro deploy - NORMAL!)
```

### ⚠️ IMPORTANTE: Configurar Root Directory

```bash
# O Railway está procurando na raiz, mas o código está em aplicativoIntermediacoes/
# SOLUÇÃO:

1. No serviço "AplicativoIntermediacoes" clique em "Settings"
2. Role até "Root Directory"
3. Digite: aplicativoIntermediacoes
4. Clique "Save"
5. O Railway fará redeploy automático e detectará o Dockerfile

# OU mova o Dockerfile para a raiz do repositório:
# (se preferir manter estrutura plana)
```

### Passo 2: Adicionar MySQL

```bash
# No Dashboard do Railway:
1. Clique em "+ New" no projeto
2. Selecione "Database" → "Add MySQL"
3. Railway criará automaticamente um banco MySQL
4. Anote as variáveis de ambiente geradas
```

### Passo 3: Configurar Variáveis de Ambiente

```bash
# No painel do serviço web (PHP):
1. Vá em "Variables"
2. Clique "Raw Editor" e cole:

DB_DRIVER=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
APP_ENV=production
APP_DEBUG=false
SESSION_LIFETIME=3600
SESSION_SECURE=true
SESSION_HTTP_ONLY=true

# Railway substituirá automaticamente as variáveis ${{MySQL.*}}
```

### Passo 4: Deploy

```bash
# Railway faz deploy automático quando você:
1. Faz push para a branch configurada (main/master)
2. Ou clica "Deploy" no dashboard

# Aguarde o build (2-5 minutos)
# Railway mostrará logs em tempo real
```

### Passo 5: Inicializar Banco de Dados

```bash
# Opção A: Via Railway CLI
railway login
railway link
railway run mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < docker/mysql/init/01-init.sql

# Opção B: Via Dashboard (MySQL Query)
# 1. Abra o serviço MySQL
# 2. Clique "Data" → "Query"
# 3. Cole o conteúdo de docker/mysql/init/01-init.sql
# 4. Execute
```

### Passo 6: Configurar Domínio

```bash
# No painel do serviço web:
1. Vá em "Settings"
2. Clique "Generate Domain" (Railway fornece domínio gratuito)
3. Ou adicione domínio customizado em "Custom Domains"

# Exemplo de domínio Railway:
# aplicativo-intermediacoes-production.up.railway.app
```

### Passo 7: Verificar Deploy

```bash
# Acesse o domínio gerado
curl https://seu-app.up.railway.app

# Ou clique em "View Logs" para acompanhar
# Login inicial: admin / admin123 (ALTERE!)
```

### Configurações Adicionais (Opcional)

#### HTTPS Automático
```
✅ Railway fornece HTTPS automático para todos os domínios
✅ Certificados SSL renovados automaticamente
✅ Não precisa configurar Let's Encrypt manualmente
```

#### Scaling
```bash
# No painel do serviço:
1. Settings → Resources
2. Ajuste CPU/RAM conforme necessário
3. Railway cobra por uso (veja pricing)
```

#### Logs em Tempo Real
```bash
# Via Dashboard:
Settings → View Logs

# Via CLI:
railway logs
```

#### Variáveis de Referência
```bash
# Para referenciar outro serviço:
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}

# Railway substitui automaticamente
```

### Troubleshooting Railway

#### Build falha com "Railpack could not determine how to build the app"
```bash
# CAUSA: Railway está na raiz do repo, mas código está em aplicativoIntermediacoes/

# SOLUÇÃO 1 (Recomendada):
1. Vá em Settings do serviço
2. Root Directory → aplicativoIntermediacoes
3. Save (redeploy automático)

# SOLUÇÃO 2 (Alternativa):
# Mova Dockerfile para raiz do repositório
mv aplicativoIntermediacoes/Dockerfile ./
# E ajuste paths no Dockerfile:
# COPY aplicativoIntermediacoes/ /var/www/html/
```

#### Build falha com "ext-gd * -> it is missing from your system"
```bash
# CAUSA: phpspreadsheet requer extensão gd do PHP

# SOLUÇÃO (JÁ APLICADA):
# O Dockerfile já instala gd, mas o Composer no stage 1 não tem acesso
# Adicionamos --ignore-platform-reqs no composer install do stage 1
# As extensões estarão disponíveis no stage final (php:8.3-apache)

# Se o erro persistir, force rebuild:
1. Settings → Redeploy
2. Ou faça um commit vazio e push
```

#### Build falha com "Dockerfile not found"
```bash
# Railway busca Dockerfile na raiz configurada
# Verifique se Dockerfile existe no Root Directory correto

# Se usar docker/php-fpm/Dockerfile para produção:
# Configure Build Command customizado em Settings
```

#### Erro: "Connection refused" ao MySQL
```bash
# Verifique variáveis de ambiente:
1. Painel do serviço → Variables
2. Confirme que DB_HOST aponta para ${{MySQL.MYSQL_HOST}}
3. Redeploy se necessário
```

#### Memória insuficiente (OOM)
```bash
# Aumente recursos:
Settings → Resources → Memory
# Recomendado: 2GB para produção
```

#### Deploy não atualiza
```bash
# Force redeploy:
1. Settings → Redeploy
2. Ou faça commit vazio: git commit --allow-empty -m "redeploy"
```

### Custos Railway (Dezembro 2025)

```
Plano Free:
- $5 de crédito/mês
- Bom para testes
- Serviços pausam após 500h/mês

Plano Pro ($20/mês):
- $20 de crédito incluído
- Usage-based pricing
- Sem limites de hora
- Suporte prioritário

Estimativa para este app:
- MySQL: ~$2-5/mês
- Web Service (1GB RAM): ~$5-10/mês
- Total: ~$7-15/mês
```

### Railway CLI (Opcional)

```bash
# Instalar
npm i -g @railway/cli

# Login
railway login

# Link ao projeto
railway link

# Ver variáveis
railway variables

# Logs
railway logs

# Run comando
railway run <command>
```

### Comparação: Railway vs DigitalOcean

| Recurso | Railway | DigitalOcean |
|---------|---------|--------------|
| Setup | 15 min | 30 min |
| HTTPS | Automático | Manual (Let's Encrypt) |
| Deploy | Git push | Docker manual |
| Preço | Usage-based ($7-15/mês) | Fixo ($4-12/droplet) |
| Scaling | Automático | Manual |
| Banco | Gerenciado | Auto-hospedado |
| Backup | Incluso (Pro) | Manual |

**Railway é ideal se:**
- ✅ Quer deploy rápido e automático
- ✅ Prefere infraestrutura gerenciada
- ✅ Não quer gerenciar servidores

**DigitalOcean é ideal se:**
- ✅ Precisa controle total do servidor
- ✅ Quer custos previsíveis
- ✅ Tem experiência com DevOps

---

## DigitalOcean Deployment (30 minutos)

### Setup Inicial (execute no servidor)

```bash
# SSH no seu droplet
ssh root@SEU_IP

# Install
curl -fsSL https://get.docker.com -o get-docker.sh | sh
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && chmod +x /usr/local/bin/docker-compose

# Clone
mkdir -p /opt && cd /opt
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
cd AplicativoIntermediacoes

# Configure
cp .env.example .env
# Edite com: nano .env
# - DB_PASSWORD: senha forte
# - APP_ENV: production
```

### Deploy

```bash
# Validar
bash validate-deployment.sh

# Executar
docker compose -f docker-compose.prod.yml up -d

# Verificar
docker compose -f docker-compose.prod.yml ps
```

### HTTPS com Let's Encrypt

```bash
# Install Certbot
apt install -y certbot

# Gerar certificado
certbot certonly --standalone -d seu-dominio.com

# Copiar para projeto
cp /etc/letsencrypt/live/seu-dominio.com/fullchain.pem ./certs/
cp /etc/letsencrypt/live/seu-dominio.com/privkey.pem ./certs/

# Reiniciar Nginx
docker compose -f docker-compose.prod.yml restart nginx
```

### Pós-Deploy

```bash
# 1. Altere senha admin
docker compose -f docker-compose.prod.yml exec php-fpm php -r "
\$password = password_hash('sua_nova_senha_forte', PASSWORD_BCRYPT);
echo 'Hash: ' . \$password;
"
# Use o hash gerado para atualizar no banco

# 2. Teste a aplicação
curl https://seu-dominio.com

# 3. Backup
docker compose -f docker-compose.prod.yml exec -T db mysqldump -u root -p${DB_PASSWORD} app_data > backup.sql

# 4. Configure auto-renew SSL
echo "0 3 * * * /usr/bin/certbot renew --quiet && docker compose -f /opt/AplicativoIntermediacoes/docker-compose.prod.yml restart nginx" | crontab -
```

## Funcionalidades Principais

✅ **Upload de Dados** - CSV/XLSX com intermediações
✅ **Painel de Negociações** - Listar registros disponíveis
✅ **Formulário de Venda** - Processar transações com cálculos cliente-side
✅ **Visualização** - Dashboard com dados negociados
✅ **Admin** - Gerenciar usuários (role-based access)
✅ **Banco de Dados** - MySQL com transações ACID
✅ **Segurança** - HTTPS, validação, proteção contra XSS/SQLi

## Fluxo de Transação

1. **Login** → autenticação via banco de dados
2. **Upload CSV/XLSX** → importa dados para tabelas
3. **Visualizar Dados** → lista negociações disponíveis
4. **Negociar** → seleciona registro e preenche formulário
5. **Calcular** → JS computa valores (vendedor, comprador, assessor)
6. **Confirmar** → salva negociação no banco
7. **Relatório** → visualiza histórico de negociações

## Troubleshooting

### Erro: "Connection refused"
```bash
docker compose -f docker-compose.prod.yml logs db
docker compose -f docker-compose.prod.yml restart db
```

### Erro: "Permission denied" em uploads
```bash
docker compose -f docker-compose.prod.yml exec php-fpm chown -R www-data:www-data /var/www/html/tmp
```

### Banco não carrega dados
```bash
docker compose -f docker-compose.prod.yml exec db mysql -u root -p${DB_PASSWORD} -e "SHOW TABLES FROM app_data;"
```

## Monitoramento

```bash
# Logs em tempo real
docker compose -f docker-compose.prod.yml logs -f php-fpm

# Status
docker compose -f docker-compose.prod.yml ps

# Usar sistema
docker compose -f docker-compose.prod.yml exec php-fpm df -h
```

## Próximos Passos

1. Leia [DEPLOYMENT.md](DEPLOYMENT.md) para configurações avançadas
2. Consulte [SECURITY.md](SECURITY.md) para hardening
3. Configure monitoramento com Sentry/DataDog
4. Implemente 2FA para admin
5. Configure backups automáticos
6. Teste a API com Postman/Insomnia

## Suporte

- Erro de Deploy? → Veja `docker compose -f docker-compose.prod.yml logs`
- Problema de Segurança? → Leia `SECURITY.md`
- Perguntas de Infraestrutura? → Consulte `DEPLOYMENT.md`

---

**Tempo estimado até go-live**: ~2 horas (setup + testes + SSL)
