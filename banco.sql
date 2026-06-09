-- Banco do bate-ponto (MySQL do XAMPP).
-- Pra usar: phpMyAdmin > Importar > escolhe esse arquivo > Executar.

CREATE DATABASE IF NOT EXISTS bate_ponto
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bate_ponto;

-- funcionarios. a matricula eh o texto que vai dentro do QR.
-- botei UNIQUE pra nao ter matricula repetida.
CREATE TABLE IF NOT EXISTS funcionarios (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(120)  NOT NULL,
    matricula VARCHAR(50)   NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- cada leitura de QR vira uma linha aqui, com a hora do servidor.
CREATE TABLE IF NOT EXISTS registros_ponto (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT       NOT NULL,
    data_hora      DATETIME  NOT NULL,
    CONSTRAINT fk_registro_funcionario
        FOREIGN KEY (funcionario_id)
        REFERENCES funcionarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- funcionarios de teste. abra o gerar-qr.html pra ter os QR
-- das matriculas M001, M002 e M003 prontos pra escanear.
INSERT INTO funcionarios (nome, matricula) VALUES
    ('Gabriel Carvalho', 'M001'),
    ('Ana Souza',        'M002'),
    ('Carlos Pereira',   'M003');
