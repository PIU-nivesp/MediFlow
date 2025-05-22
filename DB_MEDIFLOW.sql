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
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    imagem VARCHAR(150)
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

INSERT INTO `medicamentos` (`nome`, `descricao`, `codigo_barras`, `fabricante`, `lote`, `validade`, `unidade_medida`, `imagem`) VALUES
('Dipirona 1g', 'Analgésico e antipirético', '7896004700153', 'EMS', 'L002', '2026-05-15', 'comprimido', 'Dipirona1.png'),
('Amoxicilina 500mg', 'Antibiótico da classe das penicilinas', '7896094900531', 'Teuto', 'L003', '2025-11-20', 'comprimido', 'Amoxicilina500.png'),
('Ibuprofeno 400mg', 'Antiinflamatório não esteroide (AINE)', '7896422500120', 'Bayer', 'L004', '2024-10-10', 'comprimido', 'Ibuprofeno400.png'),
('Loratadina 10mg', 'Antialérgico (anti-histamínico)', '7896004710411', 'Neo Química', 'L005', '2026-01-30', 'comprimido', 'Loratadina10.png'),
('Omeprazol 20mg', 'Inibidor da bomba de prótons (antiácido)', '7896094900326', 'Teuto', 'L006', '2025-07-15', 'comprimido', 'Omeprazol20.png'),
('Paracetamol 750mg', 'Analgésico e antitérmico', '7891234560011', 'MedPharma', 'L12345', '2026-06-01', 'comprimido', 'Paracetamol750.png'),
('Captopril 25mg', 'Anti-hipertensivo', '7891234560013', 'Pharlab', 'CAP25A', '2025-12-31', 'comprimido', 'Captopril25.png'),
('Losartana 50mg', 'Anti-hipertensivo', '7891234560015', 'Medley', 'LOS50Z', '2026-09-22', 'comprimido', 'Losartana50.png'),
('Azitromicina 500mg', 'Antibiótico de largo espectro', '7891234560016', 'Pharmascience', 'AZ500B', '2025-11-30', 'comprimido', 'Azitromicina500.png'),
('Clonazepam 2mg', 'Ansiolítico e anticonvulsivante', '7891234560017', 'NeuroPharm', 'CLN2T', '2026-05-20', 'comprimido', 'Clonazepam2.png'),
('Prednisona 20mg', 'Corticoide', '7891234560018', 'InflamMed', 'PR20X', '2027-07-15', 'comprimido', 'Prednisona20.png'),
('Salbutamol 100mcg', 'Broncodilatador inalatório', '7891234560019', 'RespiraVida', 'SB100I', '2025-08-10', 'unidade', 'Salbutamol100.png'),
('Cetirizina 10mg', 'Antialérgico indicado para rinite alérgica e urticária.', '7891234560160', 'Medley', 'CET123', '2026-04-01', 'comprimido', 'Cetirizina10.png'),
('Metformina 850mg', 'Usado no tratamento de diabetes tipo 2.', '7891234560028', 'EMS', 'MET456', '2025-12-15', 'comprimido', 'Metformina850.png'),
('Enalapril 10mg', 'Anti-hipertensivo, indicado para pressão alta.', '7891234560035', 'Bayer', 'ENA789', '2026-07-10', 'comprimido', 'Enalapril10.png'),
('Hidroxicloroquina 400mg', 'Indicada para doenças autoimunes como lúpus e artrite.', '7891234560042', 'Sanofi', 'HQC321', '2027-01-30', 'comprimido', 'Hidroxicloroquina400.png'),
('Fluconazol 150mg', 'Antifúngico de dose única.', '7891234560059', 'Aché', 'FLU654', '2026-09-20', 'comprimido', 'Fluconazol150.png'),
('AAS Infantil 100mg', 'Analgésico e antipirético infantil.', '7891234560066', 'Bayer', 'AASINF01', '2026-02-01', 'comprimido', 'AASInfantil100.png'),
('Simeticona 125mg', 'Medicamento usado para aliviar gases.', '7891234560073', 'Neo Química', 'SIMT002', '2025-11-10', 'comprimido', 'Simeticona125.png'),
('Furosemida 40mg', 'Diurético para tratamento de hipertensão e retenção de líquidos.', '7891234560080', 'Teuto', 'FUR003', '2026-08-05', 'comprimido', 'Furosemida40.png'),
('Cetoconazol 200mg', 'Antifúngico de uso oral.', '7891234560097', 'EMS', 'CETO004', '2026-10-20', 'comprimido', 'Cetoconazol200.png'),
('Ranitidina 150mg', 'Redutor de acidez estomacal.', '7891234560103', 'Medley', 'RANI005', '2025-09-25', 'comprimido', 'Ranitidina150.png'),
('Nistatina 100.000 UI/ml', 'Antifúngico oral indicado para candidíase.', '7891234560110', 'Aché', 'NIST006', '2026-03-15', 'ml', 'Nistatina100000.png'),
('Levotiroxina Sodica 50mcg', 'Hormônio tireoidiano sintético.', '7891234560127', 'Sanofi', 'LEVO007', '2027-01-01', 'comprimido', 'Levotiroxinasodica50.png'),
('Domperidona 10mg', 'Anti-emético e estimulador da motilidade gástrica.', '7891234560134', 'EMS', 'DOMP008', '2026-06-30', 'comprimido', 'Domperidona10.png');


INSERT INTO `estoque` (`medicamento_id`, `quantidade`, `alerta_minimo`) VALUES
(1, 15, 10),
(2, 0, 5),
(3, 0, 10),
(4, 50, 8),
(5, 0, 12),
(6, 30, 10),
(7, 50, 20),
(8, 10, 10),
(9, 0, 20),
(10, 20, 10),
(11, 0, 20),
(12, 0, 20),
(13, 100, 20),
(14, 0, 20),
(15, 0, 20),
(16, 0, 10),
(17, 0, 10),
(18, 0, 10),
(19, 0, 10),
(20, 0, 10),
(21, 0, 10);

-- senha do ADM adm123
INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `cargo`) VALUES
('ADM', 'adm@medflow.com.br', 'fcef631eab0be0f69d940e737b136e0cbcf4f6f1de81f50822862002655af92e', 'admin'),
('Renato', 'Re@gmail.com', '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5', 'farmaceutico');