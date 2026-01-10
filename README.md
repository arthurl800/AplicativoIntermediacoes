# 📦 Sistema de Intermediações

Sistema web para gerenciamento de intermediações financeiras, negociações e auditoria completa.

## 🌟 Funcionalidades

- ✅ Upload e importação de dados via Excel
- ✅ Visualização e filtro de intermediações
- ✅ Sistema completo de negociações
- ✅ Estorno de negociações com auditoria
- ✅ Relatórios e auditorias detalhadas
- ✅ Gerenciamento de usuários e permissões
- ✅ Sistema de autenticação seguro
- ✅ Interface responsiva (mobile-friendly)

## 🚀 Deploy

Consulte o arquivo [DEPLOY_GUIDE.md](DEPLOY_GUIDE.md) para instruções detalhadas de publicação.

### Hospedagens Gratuitas Recomendadas:
- **InfinityFree** (recomendado): https://infinityfree.net
- **000webhost**: https://www.000webhost.com

## 💻 Desenvolvimento Local

### Requisitos
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Apache com mod_rewrite
- Composer

### Instalação Local

1. Clone o repositório:
```bash
git clone https://github.com/arthurl800/AplicativoIntermediacoes.git
cd AplicativoIntermediacoes
```

2. Instale dependências:
```bash
composer install
```

3. Configure o banco de dados:
```bash
mysql -u root -p < setup_database.sql
```

4. Configure variáveis de ambiente:
```bash
cp .env.example .env
# Edite .env com suas credenciais
```

5. Acesse no navegador:
```
http://localhost/aplicativoIntermediacoes
```

### Credenciais Padrão
- **Usuário:** admin
- **Senha:** admin123
- ⚠️ **Altere após primeiro acesso!**

## 📁 Estrutura do Projeto

```
aplicativoIntermediacoes/
├── app/
│   ├── controller/      # Controladores MVC
│   ├── model/           # Modelos de dados
│   ├── util/            # Utilitários e helpers
│   └── view/            # Views e templates
├── assets/
│   └── css/             # Estilos
├── config/
│   └── database.php     # Configuração do BD
├── includes/
│   ├── header.php       # Cabeçalho comum
│   └── footer.php       # Rodapé comum
├── logs/                # Logs da aplicação
├── vendor/              # Dependências Composer
├── .env                 # Variáveis de ambiente (não versionado)
├── .env.example         # Template de configuração
├── .htaccess            # Configuração Apache
├── composer.json        # Dependências PHP
├── index.php            # Front controller
├── setup_database.sql   # Script de criação do BD
└── DEPLOY_GUIDE.md      # Guia de publicação
```

## 🔒 Segurança

- Senhas com hash bcrypt
- Proteção contra SQL Injection via PDO
- Proteção contra XSS
- Validação de sessões
- Auditoria completa de ações
- Arquivo .env protegido via .htaccess

## 📊 Tecnologias

- **Backend:** PHP 8.0+
- **Banco de Dados:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Bibliotecas:**
  - PhpSpreadsheet (importação Excel)
  - ZipStream (geração de arquivos)

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

## 📝 Licença

Este projeto é de código aberto.

## 👤 Autor

arthurl800

## 🆘 Suporte

Para problemas ou dúvidas:
1. Consulte o desenvolvedor

---

**Desenvolvido com ❤️ para facilitar o gerenciamento de intermediações financeiras**
