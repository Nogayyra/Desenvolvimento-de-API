-- FixAPI — Script de criação do banco de dados

CREATE DATABASE IF NOT EXISTS fixapi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fixapi;

CREATE TABLE IF NOT EXISTS maintenance_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(150) NOT NULL,
    cliente_telefone VARCHAR(20) NOT NULL,
    equipamento VARCHAR(100) NOT NULL,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NULL,
    problema_relatado TEXT NOT NULL,
    diagnostico TEXT NULL,
    status ENUM('recebido','em_analise','em_manutencao','aguardando_peca','concluido','entregue','cancelado') NOT NULL DEFAULT 'recebido',
    valor DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Registros de exemplo
INSERT INTO maintenance_orders
    (cliente_nome, cliente_telefone, equipamento, marca, modelo, problema_relatado, diagnostico, status, valor)
VALUES
    ('João Silva', '(71) 99999-0000', 'Notebook', 'Dell', 'Inspiron 15', 'Não liga', 'Fonte queimada', 'em_manutencao', 250.00),
    ('Maria Souza', '(71) 98888-1111', 'Desktop', 'HP', 'Pavilion', 'Muito lento', NULL, 'recebido', NULL),
    ('Carlos Pereira', '(71) 97777-2222', 'Notebook', 'Lenovo', 'IdeaPad 3', 'Tela quebrada', 'Troca de tela necessária', 'aguardando_peca', 480.00),
    ('Ana Costa', '(71) 96666-3333', 'All-in-One', 'Samsung', NULL, 'Não conecta ao Wi-Fi', 'Placa de rede com defeito', 'concluido', 150.00),
    ('Pedro Lima', '(71) 95555-4444', 'Notebook', 'Acer', 'Aspire 5', 'Superaquecendo', NULL, 'entregue', 90.00);
