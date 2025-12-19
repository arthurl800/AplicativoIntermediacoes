-- Inicialização do Banco de Dados para Aplicativo de Intermediações
-- Este arquivo é executado automaticamente pelo Docker ao iniciar o container MySQL

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS USUARIOS (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Intermediações (Fonte)
CREATE TABLE IF NOT EXISTS INTERMEDIACOES (
    ID_Registro INT PRIMARY KEY AUTO_INCREMENT,
    Data_Importacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    Conta VARCHAR(50),
    Cliente VARCHAR(255),
    Produto VARCHAR(100),
    Estrategia VARCHAR(100),
    Emissor VARCHAR(50),
    Quantidade BIGINT,
    Valor_Bruto DECIMAL(18, 2),
    IR DECIMAL(18, 2),
    Valor_Liquido DECIMAL(18, 2),
    Vencimento DATE,
    Data_Compra DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Intermediações Negociada (Cópia para manipulação)
CREATE TABLE IF NOT EXISTS INTERMEDIACOES_TABLE_NEGOCIADA (
    ID_Registro INT PRIMARY KEY AUTO_INCREMENT,
    Data_Importacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    Conta VARCHAR(50),
    Cliente VARCHAR(255),
    Produto VARCHAR(100),
    Estrategia VARCHAR(100),
    Emissor VARCHAR(50),
    Quantidade_Disponivel BIGINT,
    Valor_Bruto DECIMAL(18, 2),
    IR DECIMAL(18, 2),
    Valor_Liquido DECIMAL(18, 2),
    Vencimento DATE,
    Data_Compra DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Negociações (Transações realizadas)
CREATE TABLE IF NOT EXISTS NEGOCIACOES (
    ID_Negociacao INT PRIMARY KEY AUTO_INCREMENT,
    ID_Registro_Source INT NOT NULL,
    Data_Registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Vendedor
    Conta_Vendedor VARCHAR(50),
    Nome_Vendedor VARCHAR(255),
    Quantidade_negociada BIGINT,
    Taxa_Saida DECIMAL(5, 2),
    Valor_Bruto_Importado_Raw DECIMAL(18, 2),
    Valor_Bruto_Saida DECIMAL(18, 2),
    Valor_Liquido_Saida DECIMAL(18, 2),
    Preco_Unitario_Saida DECIMAL(18, 8),
    Ganho_Saida DECIMAL(18, 2),
    Rentabilidade_Saida DECIMAL(8, 4),
    Valor_Unitario_Bruto DECIMAL(18, 8),
    Valor_Unitario_Liquido DECIMAL(18, 8),
    
    -- Comprador
    Conta_Comprador VARCHAR(50),
    Nome_Comprador VARCHAR(255),
    Taxa_Entrada DECIMAL(5, 2),
    Valor_Entrada DECIMAL(18, 2),
    Preco_Unitario_Entrada DECIMAL(18, 8),
    
    -- Assessor
    Valor_Plataforma DECIMAL(18, 2),
    Corretagem_Assessor DECIMAL(18, 2),
    Roa_Assessor DECIMAL(8, 4),
    
    -- Produto e Estratégia
    Produto VARCHAR(100),
    Estrategia VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ID_Registro_Source) REFERENCES INTERMEDIACOES(ID_Registro),
    INDEX idx_data (Data_Registro),
    INDEX idx_conta_vendedor (Conta_Vendedor),
    INDEX idx_conta_comprador (Conta_Comprador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Auditoria
CREATE TABLE IF NOT EXISTS AUDITORIA (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    acao VARCHAR(255),
    tabela VARCHAR(100),
    registro_id INT,
    dados_antes JSON,
    dados_depois JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar usuário admin padrão (senha: admin123 - ALTERE EM PRODUÇÃO!)
INSERT INTO USUARIOS (username, email, password, role) VALUES 
('admin', 'admin@localhost', '$2y$10$YIjlrKxkiUc5qj9B5.lF..9vhfQ/UtL0X1EgQI7b2b6w7z8q8c8H6', 'admin')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Criar índices para performance
CREATE INDEX idx_intermediacoes_produto ON INTERMEDIACOES(Produto);
CREATE INDEX idx_intermediacoes_estrategia ON INTERMEDIACOES(Estrategia);
CREATE INDEX idx_negociadas_disponivel ON INTERMEDIACOES_TABLE_NEGOCIADA(Quantidade_Disponivel);
CREATE INDEX idx_negociacao_data ON NEGOCIACOES(Data_Registro);
