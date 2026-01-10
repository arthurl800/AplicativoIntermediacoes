# ✅ Checklist de Deploy - Sistema de Intermediações

Use este checklist para garantir que nada seja esquecido durante a publicação.

## 📋 Antes do Upload

### Configuração Local
- [ ] Execute `php check_deploy.php` e corrija todos os erros
- [ ] Teste todas as funcionalidades localmente
- [ ] Faça backup do banco de dados local
- [ ] Exporte dados importantes se necessário

### Preparação dos Arquivos
- [ ] Configure `.env` com valores de desenvolvimento
- [ ] Verifique se `.gitignore` inclui `.env`
- [ ] Compacte os arquivos (ZIP) OU prepare para FTP
- [ ] **NÃO inclua:** `.git/`, `node_modules/`, arquivos de teste

---

## 🌐 Configuração na Hospedagem

### 1. Criação da Conta
- [ ] Conta criada na hospedagem (InfinityFree/000webhost)
- [ ] Domínio/subdomínio escolhido
- [ ] Aguardei ativação completa da conta

### 2. Banco de Dados
- [ ] Banco MySQL criado via cPanel
- [ ] Usuário MySQL criado
- [ ] Usuário associado ao banco (ALL PRIVILEGES)
- [ ] **Anotei:** Host, Nome do Banco, Usuário, Senha

#### Credenciais do Banco (anote aqui):
```
Host: _______________________
Nome: _______________________
User: _______________________
Pass: _______________________
```

### 3. Importação do Banco
- [ ] Acesso ao phpMyAdmin
- [ ] Banco de dados selecionado
- [ ] Arquivo `setup_database.sql` importado
- [ ] Todas as 6 tabelas criadas com sucesso
- [ ] Usuário admin criado (verifique na tabela USUARIOS_TABLE)

### 4. Upload dos Arquivos
- [ ] Todos os arquivos enviados para `/htdocs/`
- [ ] Estrutura de pastas preservada
- [ ] Arquivo `.htaccess` presente na raiz
- [ ] Pastas `vendor/`, `app/`, `assets/`, `config/`, `includes/` presentes

### 5. Configuração do .env
- [ ] Arquivo `.env` criado (copie de `.env.example`)
- [ ] `DB_HOST` configurado (geralmente `sqlXXX.epizy.com`)
- [ ] `DB_NAME` configurado
- [ ] `DB_USER` configurado
- [ ] `DB_PASS` configurado
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` com seu domínio
- [ ] Permissão do `.env` ajustada para 600 (somente leitura)

### 6. Ajustes no .htaccess
- [ ] `RewriteBase /` (não `/aplicativoIntermediacoes/`)
- [ ] SSL configurado (descomente redirecionamento HTTPS se ativado)
- [ ] Limites de memória ajustados conforme hospedagem

---

## 🔒 Segurança

### Primeira Configuração
- [ ] Site acessível no navegador
- [ ] Login funcionando
- [ ] Logado como admin (user: admin, pass: admin123)
- [ ] **SENHA DO ADMIN ALTERADA IMEDIATAMENTE**
- [ ] Email do admin atualizado

### Verificações de Segurança
- [ ] Arquivo `.env` não acessível pelo navegador (teste: seusite.com/.env)
- [ ] Arquivo `setup_database.sql` protegido
- [ ] Pasta `config/` não acessível
- [ ] SSL/HTTPS funcionando (ícone de cadeado)
- [ ] APP_DEBUG=false no .env

---

## 🧪 Testes em Produção

### Funcionalidades Básicas
- [ ] Login/Logout funcionando
- [ ] Dashboard carregando
- [ ] Menu de navegação funcional
- [ ] CSS e estilos carregando corretamente

### Upload e Importação
- [ ] Upload de arquivo Excel funciona
- [ ] Importação processa corretamente
- [ ] Dados aparecem na visualização
- [ ] Sem erros de memória ou timeout

### Negociações
- [ ] Formulário de negociação abre
- [ ] Cálculos automáticos funcionam
- [ ] Negociação é salva corretamente
- [ ] Aparece na lista de negociações

### Estorno e Auditoria
- [ ] Botão de estorno funciona
- [ ] Quantidade retorna ao estoque
- [ ] Auditoria registra estorno
- [ ] Relatórios de auditoria funcionam

### Responsividade
- [ ] Testado em desktop
- [ ] Testado em tablet (se disponível)
- [ ] Testado em smartphone
- [ ] Todos os elementos visíveis e funcionais

---

## 📊 Pós-Deploy

### Backup
- [ ] Exportar banco de dados (phpMyAdmin > Export)
- [ ] Baixar arquivos via FTP/File Manager
- [ ] Guardar backups em local seguro
- [ ] Agendar backups regulares

### Monitoramento
- [ ] Verificar logs de erro (`File Manager > logs/`)
- [ ] Testar performance (tempo de carregamento)
- [ ] Verificar limites da hospedagem não ultrapassados

### Documentação
- [ ] Credenciais anotadas em local seguro
- [ ] URL do site documentada
- [ ] Instruções de acesso compartilhadas (se necessário)

---

## 🎯 Usuários e Dados

### Criação de Usuários
- [ ] Criar usuários adicionais se necessário
- [ ] Atribuir permissões corretas (admin/user)
- [ ] Compartilhar credenciais de forma segura

### Importação de Dados Reais
- [ ] Arquivo Excel preparado
- [ ] Dados importados com sucesso
- [ ] Validar quantidade de registros
- [ ] Verificar se não há duplicatas

---

## ✅ Checklist Final

- [ ] Sistema acessível publicamente
- [ ] Todas as funcionalidades testadas
- [ ] Nenhum erro crítico nos logs
- [ ] Senhas padrão alteradas
- [ ] Backups realizados
- [ ] SSL/HTTPS ativo
- [ ] Performance aceitável
- [ ] Usuários criados e testados

---

## 🆘 Em Caso de Problemas

### Erro 500
1. Verifique logs em `File Manager > logs/`
2. Ative temporariamente `APP_DEBUG=true` no `.env`
3. Recarregue a página e veja erro detalhado
4. Desative debug após identificar problema

### Erro de Banco de Dados
1. Verifique credenciais no `.env`
2. Confirme host (não use `localhost`, use host fornecido)
3. Teste conexão via phpMyAdmin

### Upload Falha
1. Verifique limites no `.htaccess`
2. Reduza tamanho do arquivo
3. Verifique se pasta tem permissão de escrita

### Página em Branco
1. Verifique se `vendor/autoload.php` existe
2. Execute `composer install` se necessário
3. Verifique logs do PHP

---

## 📞 Suporte

- **Documentação:** DEPLOY_GUIDE.md
- **Script de verificação:** `php check_deploy.php`
- **Logs:** File Manager > logs/
- **Hospedagem:** Suporte via ticket na InfinityFree/000webhost

---

**🎉 Parabéns pelo deploy!**

Data do deploy: _______________
URL do site: _______________
Responsável: _______________
