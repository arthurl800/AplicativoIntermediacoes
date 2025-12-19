# Aplicativo de Intermediações de Títulos - Guia de Deployment

## Visão Geral

Sistema web para gerenciamento de intermediações de títulos, com suporte para:
- Upload de dados (CSV/XLSX)
- Gerenciamento de negociações
- Dashboard administrativo
- Controle de acesso por roles (admin/user)
- Banco de dados MySQL com transações ACID

## Pré-requisitos

### Para Desenvolvimento Local
- Docker 20.10+
- Docker Compose 1.29+
- Git

### Para Produção (DigitalOcean)
- Droplet Ubuntu 22.04 LTS (4GB RAM mínimo recomendado)
- SSH access
- Domínio configurado

## Desenvolvimento Local

### 1. Clone o Repositório
```bash
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
cd AplicativoIntermediacoes
```

### 2. Configure as Variáveis de Ambiente
```bash
cp .env.example .env
# Edite .env com suas configurações locais (padrões funcionam para dev)
```

### 3. Inicie com Docker Compose
```bash
docker compose up --build
```

### 4. Acesse a Aplicação
- URL: http://localhost:8000
- Login: `admin` / `admin123` (ALTERE em produção!)

## Deployment em DigitalOcean

### Passo 1: Criar e Configurar o Droplet

```bash
# SSH no seu droplet
ssh root@seu_droplet_ip

# Atualize o sistema
apt update && apt upgrade -y

# Instale Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instale Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verifique instalação
docker --version
docker-compose --version
```

### Passo 2: Clone o Repositório no Servidor

```bash
# No servidor DigitalOcean
cd /opt
sudo git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
sudo chown -R $USER:$USER /opt/AplicativoIntermediacoes
cd /opt/AplicativoIntermediacoes
```

### Passo 3: Configure Variáveis de Produção

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite com valores seguros
nano .env
```

**Valores críticos a alterar:**
```env
DB_PASSWORD=your_strong_password_here
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
```

### Passo 4: Gere Certificado SSL (Recomendado)

```bash
# Instale Certbot
sudo apt install certbot -y

# Gere certificado para seu domínio
sudo certbot certonly --standalone -d seu-dominio.com

# Copie certificados para o projeto
sudo cp /etc/letsencrypt/live/seu-dominio.com/fullchain.pem ./certs/
sudo cp /etc/letsencrypt/live/seu-dominio.com/privkey.pem ./certs/
sudo chown $USER:$USER ./certs/*
```

### Passo 5: Configure Nginx com SSL

Edite `docker/nginx/default.conf` e adicione:

```nginx
server {
    listen 443 ssl http2;
    server_name seu-dominio.com;
    
    ssl_certificate /etc/nginx/certs/fullchain.pem;
    ssl_certificate_key /etc/nginx/certs/privkey.pem;
    
    # ... resto da configuração
}

# Redirecionar HTTP para HTTPS
server {
    listen 80;
    server_name seu-dominio.com;
    return 301 https://$server_name$request_uri;
}
```

### Passo 6: Inicialize o Sistema

```bash
# Use a composição de produção
docker compose -f docker-compose.prod.yml up -d

# Verifique status
docker compose -f docker-compose.prod.yml ps

# Veja logs
docker compose -f docker-compose.prod.yml logs -f
```

### Passo 7: Ajuste Permissões e Verificações

```bash
# Criar diretórios necessários
mkdir -p ./tmp ./logs
sudo chown -R www-data:www-data ./tmp ./logs

# Verificar conectividade do banco
docker compose -f docker-compose.prod.yml exec php-fpm php -r "
try {
    \$db = new PDO('mysql:host=db;dbname=app_data', 'root', getenv('DB_PASSWORD'));
    echo 'Conexão com banco OK\n';
} catch (Exception \$e) {
    echo 'Erro: ' . \$e->getMessage() . '\n';
}
"
```

## Testes e Validações

### Teste a Aplicação
```bash
# Acesse via browser
https://seu-dominio.com

# Ou teste via curl
curl -u admin:admin123 https://seu-dominio.com
```

### Fluxo de Teste Completo
1. **Login**: Use credenciais `admin` / `admin123`
2. **Upload**: Importe um arquivo CSV/XLSX com dados de intermediações
3. **Visualize**: Acesse "Dados" para ver registros importados
4. **Negocie**: Clique em "Negociar" e processe uma venda
5. **Verifique BD**: Confirme que dados foram salvos

```bash
# Conectar ao banco de dados
docker compose -f docker-compose.prod.yml exec db mysql -p${DB_PASSWORD} -e "USE app_data; SELECT * FROM NEGOCIACOES LIMIT 5;"
```

## Monitoramento e Manutenção

### Logs
```bash
# Logs da aplicação
docker compose -f docker-compose.prod.yml logs php-fpm

# Logs do Nginx
docker compose -f docker-compose.prod.yml logs nginx

# Logs do MySQL
docker compose -f docker-compose.prod.yml logs db
```

### Backup do Banco de Dados
```bash
# Backup
docker compose -f docker-compose.prod.yml exec db mysqldump -u root -p${DB_PASSWORD} app_data > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore
docker compose -f docker-compose.prod.yml exec -T db mysql -u root -p${DB_PASSWORD} app_data < backup_file.sql
```

### Atualizar a Aplicação
```bash
cd /opt/AplicativoIntermediacoes
git pull origin main
docker compose -f docker-compose.prod.yml up --build -d
```

## Troubleshooting

### Erro: "Connection refused" ao banco de dados
```bash
# Verificar se container do DB está rodando
docker compose -f docker-compose.prod.yml ps

# Reiniciar containers
docker compose -f docker-compose.prod.yml restart
```

### Erro: "Permission denied" para uploads
```bash
# Ajustar permissões
docker compose -f docker-compose.prod.yml exec php-fpm chown -R www-data:www-data /var/www/html/tmp
```

### Memória insuficiente
```bash
# Aumentar limite no php-fpm
# Edite docker/php-fpm/Dockerfile e ajuste memory_limit
```

## Segurança - Checklist

- [ ] Altere senha padrão do admin (`admin123`)
- [ ] Configure SSL/TLS (Let's Encrypt)
- [ ] Configure firewall do DigitalOcean (bloquear portas desnecessárias)
- [ ] Ative backups automáticos do banco
- [ ] Monitore logs regularmente
- [ ] Use senhas fortes para credenciais do banco
- [ ] Mantenha dependências PHP atualizadas (`composer update --no-dev`)
- [ ] Configure rate limiting no Nginx
- [ ] Use HTTPS obrigatoriamente
- [ ] Implemente 2FA se possível

## Performance e Otimizações

### Para DigitalOcean (Droplet 4GB)
```bash
# Configurar swap para melhor performance
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
```

### Compressão Gzip (já configurada)
Verificar: `curl -H "Accept-Encoding: gzip" -I https://seu-dominio.com`

### Cache HTTP
Configurado em `docker/nginx/default.conf` para arquivos estáticos

## Scripts Úteis

### Deploy Automático
Crie `deploy.sh`:
```bash
#!/bin/bash
cd /opt/AplicativoIntermediacoes
git pull origin main
docker compose -f docker-compose.prod.yml up --build -d
docker compose -f docker-compose.prod.yml exec db mysql -u root -p${DB_PASSWORD} app_data < schema.sql 2>/dev/null || true
echo "Deploy concluído!"
```

### Health Check
```bash
#!/bin/bash
curl -s -o /dev/null -w "%{http_code}" https://seu-dominio.com
```

## Suporte e Documentação Adicional

- Documentação Docker: https://docs.docker.com/
- DigitalOcean Docs: https://docs.digitalocean.com/
- PHP 8.1 Docs: https://www.php.net/docs.php
- MySQL 8.0 Docs: https://dev.mysql.com/doc/

## Versão da Aplicação
- Base de Dados: MySQL 8.0
- PHP: 8.1-FPM
- Nginx: 1.25
- Composer: 2.x

---

**Versão**: 1.0.0
**Data**: Dezembro 2025
**Mantedor**: Arthur L. (arthurl800@github)
