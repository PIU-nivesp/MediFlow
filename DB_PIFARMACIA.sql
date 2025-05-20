-- Banco de Dados: DB_MEDIFLOW

CREATE DATABASE IF NOT EXISTS DB_MEDIFLOW;
USE DB_MEDIFLOW;


-- Tabela usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    cargo ENUM('farmaceutico', 'admin', 'tecnico') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela medicamentos
CREATE TABLE medicamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    codigo_barras VARCHAR(80) UNIQUE,
    fabricante VARCHAR(100) NOT NULL,
    lote VARCHAR(200) NOT NULL,
    validade DATE,
    unidade_medida ENUM('comprimido', 'ml', 'mg', 'unidade') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela fornecedores
CREATE TABLE fornecedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    contato VARCHAR(100),
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela estoque
CREATE TABLE estoque (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicamento_id INT NOT NULL,
    quantidade INT NOT NULL,
    alerta_minimo INT NOT NULL DEFAULT 10,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE
);

-- Tabela receitas
CREATE TABLE receitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paciente_nome VARCHAR(150) NOT NULL,
    medico_nome VARCHAR(150) NOT NULL,
    crm_medico VARCHAR(20) NOT NULL,
    data_prescricao DATE NOT NULL,
    observacao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela movimentacoes
CREATE TABLE movimentacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicamento_id INT NOT NULL,
    usuario_id INT NULL,
    receita_id INT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE SET NULL
);

-- Tabela versoes
CREATE TABLE versoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tabela_afetada VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    usuario_id INT,
    alteracao TEXT NOT NULL,
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela consumo
CREATE TABLE consumo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicamento_id INT NOT NULL,
    periodo ENUM('diario', 'semanal', 'mensal') NOT NULL,
    quantidade_consumida INT NOT NULL,
    receita_id INT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE SET NULL
);


-- Procedure: registrar_consumo (execute separadamente no phpMyAdmin)

DROP PROCEDURE IF EXISTS registrar_consumo;

DELIMITER //

CREATE PROCEDURE registrar_consumo()
BEGIN
    -- Inserir consumo semanal
    INSERT INTO consumo (medicamento_id, periodo, quantidade_consumida, receita_id, data_registro)
    SELECT medicamento_id, 'semanal', SUM(quantidade), receita_id, NOW()
    FROM movimentacoes
    WHERE tipo = 'saida' AND data_movimentacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY medicamento_id, receita_id;

    -- Inserir consumo mensal
    INSERT INTO consumo (medicamento_id, periodo, quantidade_consumida, receita_id, data_registro)
    SELECT medicamento_id, 'mensal', SUM(quantidade), receita_id, NOW()
    FROM movimentacoes
    WHERE tipo = 'saida' AND data_movimentacao >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    GROUP BY medicamento_id, receita_id;
END //

DELIMITER ;


-- Eventos (execute separadamente e somente se tiver permissão para EVENT)

CREATE EVENT IF NOT EXISTS atualizar_consumo_semanal
ON SCHEDULE EVERY 1 WEEK
DO CALL registrar_consumo();

CREATE EVENT IF NOT EXISTS atualizar_consumo_mensal
ON SCHEDULE EVERY 1 MONTH
DO CALL registrar_consumo();
