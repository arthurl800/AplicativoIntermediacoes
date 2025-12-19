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
