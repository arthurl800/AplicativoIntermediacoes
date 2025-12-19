# Aplicativo de Intermediações de Títulos

**Status**: ✅ Pronto para Produção  
**Versão**: 1.0.0  
**Data**: Dezembro 2025  
**Ambiente**: Docker (Dev + Prod)

---

## 📋 Visão Geral

Sistema web de **gerenciamento de intermediações de títulos** com:
- ✅ Upload de dados (CSV/XLSX) com processamento em lote
- ✅ Painel de negociações com visualização responsiva
- ✅ Formulário de negociação com cálculos cliente-side (JS)
- ✅ Persistência de transações em banco de dados MySQL
- ✅ Dashboard administrativo com controle de acesso
- ✅ Autenticação segura com bcrypt + sessões
- ✅ Pronto para deploy em DigitalOcean ou qualquer host

---

## 🚀 Quick Start

### Local (Docker Compose)
```bash
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
cd AplicativoIntermediacoes
docker compose up --build
# → Acesso: http://localhost:8000
# → Login: admin / admin123
```

### Produção (DigitalOcean)
```bash
# No droplet Ubuntu 22.04:
curl -fsSL https://get.docker.com | sh
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git /opt/app
cd /opt/app
cp .env.example .env
# → Edite .env com credenciais seguras
docker compose -f docker-compose.prod.yml up -d
# → Acesso: https://seu-dominio.com
```

**Tempo estimado**: ~30 minutos até go-live

---

## 📁 Estrutura do Projeto

```
aplicativoIntermediacoes/
├── app/
│   ├── controller/          # Controllers (roteamento, lógica)
│   │   ├── AuthController.php
│   │   ├── UploadController.php
│   │   ├── NegociacaoController.php
│   │   ├── DataController.php
│   │   └── ...
│   ├── model/               # Models (banco de dados)
│   │   ├── UserModel.php
│   │   ├── IntermediacaoModel.php
│   │   ├── NegociacaoModel.php
│   │   └── ...
│   ├── util/                # Utilitários
│   │   ├── Database.php     # PDO Singleton
│   │   ├── AuthManager.php  # Autenticação
│   │   ├── CsvProcessor.php
│   │   ├── XlsxProcessor.php
│   │   └── ...
│   └── view/                # Templates PHP
│       ├── auth/
│       ├── dados/
│       ├── negociacoes/
│       └── ...
├── config/
│   └── Config.php           # Configurações (.env)
├── docker/
│   ├── php-fpm/Dockerfile
│   ├── nginx/default.conf
│   └── mysql/init/01-init.sql
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── responsive-table.css
├── assets/
│   └── css/
├── vendor/                  # Composer dependencies
├── Dockerfile               # Dev (Apache + PHP)
├── docker-compose.yml       # Dev (Apache + MySQL)
├── docker-compose.prod.yml  # Produção (Nginx + PHP-FPM + MySQL)
├── index.php                # Roteador principal
├── .env.example             # Variáveis de exemplo
├── QUICKSTART.md            # Guia rápido (5 min)
├── DEPLOYMENT.md            # Guia completo DigitalOcean
├── SECURITY.md              # Checklist de segurança
├── validate-deployment.sh   # Script de validação
└── README.md                # Este arquivo
```

---

## 🔧 Funcionalidades Implementadas

### 1️⃣ Autenticação & Autorização
```
✅ Login com username/password
✅ Hashing bcrypt (password_verify)
✅ Sessões HTTP-only
✅ Roles: admin, user
✅ Proteção de rotas por role
✅ Logout com limpeza de sessão
```

### 2️⃣ Upload de Dados
```
✅ Suporte CSV (.csv) e Excel (.xlsx, .xls)
✅ Processamento em lote (centenas de registros)
✅ Validação de tipos e formatos
✅ Importação transacional (tudo ou nada)
✅ Dupla inserção: INTERMEDIACOES + INTERMEDIACOES_TABLE_NEGOCIADA
✅ Mensagem de sucesso com contagem de linhas
```

### 3️⃣ Painel de Negociações
```
✅ Listagem de intermediações disponíveis
✅ Filtros por produto/estratégia
✅ Links para formulário de negociação
✅ Visualização responsiva
```

### 4️⃣ Formulário de Negociação
```
✅ Pré-preenchimento com dados da intermediação
✅ Campos de entrada: quantidades, valores, taxas
✅ Cálculos em JavaScript (client-side):
   - Valor Unitário Bruto/Líquido
   - Preço Unitário (Vendedor)
   - Ganho e Rentabilidade (Vendedor)
   - Preço Unitário (Comprador)
   - Corretagem e ROA (Assessor)
✅ Botão "Calcular" para atualizar preview
✅ Validação de quantidade disponível
✅ Submit para salvar em banco
```

### 5️⃣ Visualização de Dados
```
✅ Dashboard com estatísticas
✅ Tabela responsiva de negociações
✅ Modo tela cheia
✅ Filtros por período
✅ Exibição de dados por Vendedor/Comprador/Plataforma
```

### 6️⃣ Banco de Dados
```
✅ MySQL 8.0 (compatível com MariaDB)
✅ Tabelas: USUARIOS, INTERMEDIACOES, INTERMEDIACOES_TABLE_NEGOCIADA, NEGOCIACOES, AUDITORIA
✅ Transações ACID (multi-row insert na importação)
✅ Índices para performance
✅ Charset UTF-8MB4 (suporta emojis)
✅ Constraints e foreign keys
```

### 7️⃣ Segurança
```
✅ SQL Injection: Prepared Statements (PDO)
✅ XSS: htmlspecialchars() em todas as views
✅ CSRF: AuthManager + validação de sessão
✅ File Upload: validação de tipo + limite de tamanho
✅ Error Reporting: desabilitado em produção
✅ HTTPS: certificados SSL/TLS recomendados
✅ Headers: X-Frame-Options, X-Content-Type-Options, etc.
✅ Senhas: bcrypt com salt automático
```

---

## 🏗️ Arquitetura

### Padrão MVC
```
Request → index.php → Router → Controller → Model → View → Response
```

### Stack de Produção
```
Client (Browser)
    ↓
Nginx (Reverse Proxy + SSL/TLS)
    ↓
PHP-FPM (Application Server)
    ↓
MySQL (Database)
    ↓
Volumes (Dados Persistentes)
```

### Containerização (Docker)
- **Desenvolvimento**: Apache + PHP + MySQL (docker-compose.yml)
- **Produção**: Nginx + PHP-FPM + MySQL (docker-compose.prod.yml)
- **Isolamento**: Networks e volumes separados

---

## 📊 Fluxo de Transação

```
1. Usuário faz LOGIN
   ↓
2. Uploda arquivo CSV/XLSX
   ↓
3. Dados importados para INTERMEDIACOES + INTERMEDIACOES_TABLE_NEGOCIADA
   ↓
4. Usuário visualiza "Dados Disponíveis"
   ↓
5. Clica em "Negociar" → abre Formulário
   ↓
6. Preenche valores e clica "Calcular"
   ↓
7. JS computa: ganho, preços unitários, ROA, corretagem
   ↓
8. Clica "Confirmar Venda"
   ↓
9. POST para processar → Controller valida e salva em NEGOCIACOES
   ↓
10. Redirecionador para "Negociações Realizadas" (ViewNegociadas)
   ↓
11. Exibe histórico com todos os campos computados
```

---

## 🔐 Segurança em Produção

### Checklist Implementado
- [x] Autenticação com bcrypt
- [x] Prepared Statements (sem SQL injection)
- [x] htmlspecialchars() (sem XSS)
- [x] Validação de entrada
- [x] Headers de segurança
- [x] HTTPS/SSL recomendado
- [x] Configurações via .env
- [x] Logs separados
- [x] Permissões de arquivo

### Recomendações Adicionais
- [ ] CSRF tokens em formulários (próxima versão)
- [ ] 2FA para admin
- [ ] Rate limiting
- [ ] WAF (ModSecurity ou CloudFlare)
- [ ] Monitoramento (Sentry, DataDog)
- [ ] Backups automáticos

Veja [SECURITY.md](SECURITY.md) para checklist completo.

---

## 📦 Dependências

### Backend
- PHP 8.1
- MySQL 8.0 / MariaDB 10.6+
- Composer 2.x
- PhpSpreadsheet (para Excel)

### Frontend
- Vanilla JavaScript (sem frameworks)
- CSS responsivo (incluso)
- Browser moderno (Chrome, Firefox, Safari)

### DevOps
- Docker 20.10+
- Docker Compose 1.29+
- Nginx 1.25+
- OpenSSL (SSL/TLS)

---

## 📚 Documentação

| Documento | Propósito |
|-----------|----------|
| [QUICKSTART.md](QUICKSTART.md) | Deploy em 5-30 minutos |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Guia completo DigitalOcean |
| [SECURITY.md](SECURITY.md) | Checklist de segurança |
| [validate-deployment.sh](validate-deployment.sh) | Script de pré-validação |

---

## 🧪 Testes

### Teste Local
```bash
docker compose up --build
# Acesso: http://localhost:8000
# 1. Login: admin / admin123
# 2. Upload: selecione arquivo CSV/XLSX
# 3. Visualize dados importados
# 4. Clique "Negociar" e teste fluxo completo
```

### Teste de Banco de Dados
```bash
docker compose exec db mysql -u root -p${DB_PASSWORD} app_data -e "SELECT * FROM NEGOCIACOES LIMIT 5;"
```

### Teste de Load (opcional)
```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost:8000/

# Wrk
wrk -t12 -c400 -d30s http://localhost:8000/
```

---

## 🚨 Troubleshooting

### Erro: "Connection refused"
```bash
# Verifique se containers estão rodando
docker compose ps

# Reinicie
docker compose down && docker compose up -d
```

### Erro: "Permission denied" em uploads
```bash
# Ajuste permissões
docker compose exec php-fpm chown -R www-data:www-data /var/www/html/tmp
```

### Banco de dados não carrega dados
```bash
# Verifique tabelas
docker compose exec db mysql -u root -p${DB_PASSWORD} app_data -e "SHOW TABLES;"

# Verifique logs
docker compose logs db
```

Veja [DEPLOYMENT.md](DEPLOYMENT.md) para mais soluções.

---

## 📈 Performance

### Otimizações Implementadas
- ✅ Gzip compression (nginx)
- ✅ Static file caching (365 dias)
- ✅ Optimized autoload (Composer)
- ✅ Connection pooling (PDO)
- ✅ Query optimization (índices)
- ✅ Prepared statements (reutilização de planos)

### Recomendações
- Use CDN para assets estáticos
- Configure rate limiting
- Monitore com Prometheus + Grafana
- Implemente cache layer (Redis)

---

## 🔄 CI/CD (Opcional)

### GitHub Actions
```yaml
# .github/workflows/deploy.yml
on: [push]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Validate Deployment
        run: bash validate-deployment.sh
      - name: Deploy
        run: docker compose -f docker-compose.prod.yml up -d
```

---

## 📞 Suporte & Contato

- **Issues**: https://github.com/arthurl800/AplicativoIntermediacoes/issues
- **Email**: arthurl800@github
- **Documentação**: Veja pasta do projeto

---

## 📄 Licença

Proprietary - Todos os direitos reservados © 2025

---

## ✅ Checklist Final (Antes do Deploy)

- [ ] `.env` configurado com valores seguros
- [ ] `docker-compose.prod.yml` revisado
- [ ] SSL/TLS certificado gerado
- [ ] Senha admin alterada
- [ ] Backups configurados
- [ ] Testes funcionais completos
- [ ] Script de validação executado com sucesso
- [ ] Firewall do DigitalOcean configurado
- [ ] Monitoramento ativo
- [ ] Domínio apontando para servidor

**Comando final de deployment**:
```bash
bash validate-deployment.sh && docker compose -f docker-compose.prod.yml up -d
```

---

**Versão**: 1.0.0  
**Status**: ✅ Produção-Ready  
**Última Atualização**: 19 de Dezembro de 2025
