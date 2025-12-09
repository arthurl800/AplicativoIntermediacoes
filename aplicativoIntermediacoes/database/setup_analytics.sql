-- ============================================================================
-- AUDITORIA E ANALYTICS PARA NEGOCIAÇÕES
-- ============================================================================

-- Tabela de Auditoria: Rastreia cada inserção/atualização/exclusão em NEGOCIACOES
CREATE TABLE IF NOT EXISTS NEGOCIACOES_AUDITORIA (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    negociacao_id INT NOT NULL,
    acao VARCHAR(10) NOT NULL COMMENT 'INSERT, UPDATE, DELETE',
    usuario_ip VARCHAR(45) DEFAULT NULL COMMENT 'IP do usuário que realizou a ação',
    usuario_name VARCHAR(255) DEFAULT NULL COMMENT 'Usuário que realizou a ação',
    data_acao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dados_antes JSON DEFAULT NULL COMMENT 'Valores antes da alteração (para UPDATE)',
    dados_depois JSON DEFAULT NULL COMMENT 'Valores depois da alteração',
    descricao_mudanca TEXT DEFAULT NULL COMMENT 'Resumo da mudança realizada',
    INDEX idx_negociacao_id (negociacao_id),
    INDEX idx_data_acao (data_acao),
    INDEX idx_acao (acao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- VIEWS ANALÍTICAS PARA DASHBOARD
-- ============================================================================

-- View 1: Negociações agrupadas por Operador
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_OPERADOR AS
SELECT 
    Nome_Vendedor AS operador,
    COUNT(*) AS total_negociacoes,
    SUM(Quantidade_negociada) AS quantidade_total,
    SUM(Valor_Bruto_Saida) AS valor_saida_total,
    SUM(Corretagem_Assessor) AS corretagem_total,
    AVG(Roa_Assessor) AS roa_medio,
    AVG(Rentabilidade_Saida) AS rentabilidade_media
FROM NEGOCIACOES
GROUP BY Nome_Vendedor
ORDER BY valor_saida_total DESC;

-- View 2: Negociações agrupadas por Produto
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_PRODUTO AS
SELECT 
    Produto,
    COUNT(*) AS total_negociacoes,
    SUM(Quantidade_negociada) AS quantidade_total,
    SUM(Valor_Bruto_Saida) AS valor_saida_total,
    SUM(Corretagem_Assessor) AS corretagem_total,
    AVG(Roa_Assessor) AS roa_medio,
    AVG(Rentabilidade_Saida) AS rentabilidade_media
FROM NEGOCIACOES
GROUP BY Produto
ORDER BY valor_saida_total DESC;

-- View 3: Negociações por Data
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_DATA AS
SELECT 
    DATE(Data_Registro) AS data_negociacao,
    COUNT(*) AS total_negociacoes,
    SUM(Quantidade_negociada) AS quantidade_total,
    SUM(Valor_Bruto_Saida) AS valor_saida_total,
    SUM(Corretagem_Assessor) AS corretagem_total,
    AVG(Roa_Assessor) AS roa_medio
FROM NEGOCIACOES
GROUP BY DATE(Data_Registro)
ORDER BY data_negociacao DESC;

-- View 4: Resumo Executivo (KPIs principais)
CREATE OR REPLACE VIEW VW_RESUMO_EXECUTIVO_NEGOCIACOES AS
SELECT 
    COUNT(*) AS total_negociacoes,
    SUM(Quantidade_negociada) AS quantidade_total,
    SUM(Valor_Bruto_Saida) AS valor_saida_total,
    AVG(Valor_Bruto_Saida) AS valor_medio_negociacao,
    SUM(Corretagem_Assessor) AS corretagem_total,
    AVG(Roa_Assessor) AS roa_medio,
    AVG(Rentabilidade_Saida) AS rentabilidade_media,
    COUNT(DISTINCT Nome_Comprador) AS clientes_unicos,
    COUNT(DISTINCT Produto) AS produtos_unicos,
    MAX(Data_Registro) AS ultima_negociacao
FROM NEGOCIACOES;
