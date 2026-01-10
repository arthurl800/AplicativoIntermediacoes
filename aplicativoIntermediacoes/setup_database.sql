-- Script de criação do banco de dados para produção
-- Execute este script no cPanel ou PHPMyAdmin da sua hospedagem

-- 1. CRIAR BANCO DE DADOS (substitua 'usuario_intermediacoes' pelo prefixo da sua hospedagem)
-- CREATE DATABASE IF NOT EXISTS usuario_intermediacoes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE usuario_intermediacoes;

-- 2. TABELA DE USUÁRIOS
CREATE TABLE IF NOT EXISTS USUARIOS_TABLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    Nome VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE INTERMEDIAÇÕES (dados importados)
CREATE TABLE IF NOT EXISTS INTERMEDIACOES_TABLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Conta VARCHAR(50),
    Nome VARCHAR(100),
    Mercado VARCHAR(50),
    Sub_Mercado VARCHAR(50),
    Ativo VARCHAR(100),
    Produto VARCHAR(100),
    CNPJ VARCHAR(20),
    Emissor VARCHAR(100),
    Data_Compra DATE,
    Taxa_Compra DECIMAL(10, 2),
    Taxa_Emissao DECIMAL(10, 2),
    Vencimento DATE,
    Quantidade INT,
    Valor_Bruto DECIMAL(15, 2),
    IR DECIMAL(15, 2),
    IOF DECIMAL(15, 2),
    Valor_Liquido DECIMAL(15, 2),
    Estrategia VARCHAR(100),
    Escritorio VARCHAR(100),
    Data_Registro DATE,
    Data_Cotizacao_Prev DATE,
    Tipo_Plano VARCHAR(50),
    ID_Registro VARCHAR(100),
    imported_at DATETIME,
    -- Índice único composto para evitar duplicatas
    UNIQUE INDEX idx_unique_registro (Ativo, Data_Compra, Quantidade),
    INDEX idx_conta (Conta),
    INDEX idx_ativo (Ativo),
    INDEX idx_vencimento (Vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABELA DE NEGOCIAÇÕES
CREATE TABLE IF NOT EXISTS NEGOCIACOES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ID_Registro_Source INT,
    conta_vendedor VARCHAR(50),
    nome_vendedor VARCHAR(100),
    conta_comprador VARCHAR(50),
    nome_comprador VARCHAR(100),
    ativo VARCHAR(100),
    produto VARCHAR(100),
    estrategia VARCHAR(100),
    emissor VARCHAR(100),
    vencimento DATE,
    quantidade_negociada INT,
    taxa_saida DECIMAL(10, 2),
    valor_bruto_saida DECIMAL(15, 2),
    valor_liquido_saida DECIMAL(15, 2),
    taxa_entrada DECIMAL(10, 2),
    valor_bruto_entrada DECIMAL(15, 2),
    valor_plataforma DECIMAL(15, 2),
    preco_unitario_saida DECIMAL(15, 2),
    ganho_saida DECIMAL(15, 2),
    rentabilidade_saida DECIMAL(10, 2),
    preco_unitario_entrada DECIMAL(15, 2),
    corretagem_assessor DECIMAL(15, 2),
    roa_assessor DECIMAL(10, 2),
    usuario_responsavel VARCHAR(50),
    data_negociacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estornado TINYINT(1) DEFAULT 0,
    data_estorno TIMESTAMP NULL,
    usuario_estorno VARCHAR(50) NULL,
    FOREIGN KEY (ID_Registro_Source) REFERENCES INTERMEDIACOES_TABLE(id),
    INDEX idx_data_negociacao (data_negociacao),
    INDEX idx_conta_vendedor (conta_vendedor),
    INDEX idx_conta_comprador (conta_comprador),
    INDEX idx_estornado (estornado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE AUDITORIA DE NEGOCIAÇÕES
CREATE TABLE IF NOT EXISTS NEGOCIACOES_AUDITORIA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negociacao_id INT,
    usuario_name VARCHAR(50),
    acao ENUM('INSERT', 'UPDATE', 'DELETE', 'ESTORNO'),
    detalhes TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (negociacao_id) REFERENCES NEGOCIACOES(id) ON DELETE CASCADE,
    INDEX idx_negociacao (negociacao_id),
    INDEX idx_data_hora (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABELA DE AUDITORIA GERAL DO SISTEMA
CREATE TABLE IF NOT EXISTS AUDITORIA_SISTEMA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_name VARCHAR(50),
    acao VARCHAR(100),
    modulo VARCHAR(50),
    detalhes TEXT,
    ip_address VARCHAR(45),
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_name),
    INDEX idx_modulo (modulo),
    INDEX idx_data_hora (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CRIAR USUÁRIO ADMINISTRADOR PADRÃO
-- Senha: admin123 (MUDE ISSO APÓS O PRIMEIRO LOGIN!)
INSERT INTO USUARIOS_TABLE (username, email, password_hash, Nome, role) 
VALUES (
    'admin', 
    'admin@intermediacoes.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Administrador', 
    'admin'
) ON DUPLICATE KEY UPDATE username=username;

-- 8. CONCLUÍDO
-- Anote as credenciais do banco de dados fornecidas pela sua hospedagem
-- e configure-as no arquivo .env do seu projeto
