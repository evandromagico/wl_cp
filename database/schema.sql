CREATE DATABASE IF NOT EXISTS maquetaria_db;
USE maquetaria_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'projetista', 'montador') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_entrega DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Em dia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipe_projeto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT,
    usuario_id INT,
    tipo_trabalho ENUM('desenho_torre', 'desenho_embasamento', 'montagem_torre', 'montagem_embasamento') NOT NULL,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE status_desenho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT,
    torre_status ENUM('Não Enviado', 'Enviado', 'Em Revisão', 'Concluído') DEFAULT 'Não Enviado',
    embasamento_status ENUM('Não Enviado', 'Enviado', 'Em Revisão', 'Concluído') DEFAULT 'Não Enviado',
    internos_torre_status ENUM('Não Enviado', 'Enviado', 'Concluído') DEFAULT 'Não Enviado',
    internos_embasamento_status ENUM('Não Enviado', 'Enviado', 'Concluído') DEFAULT 'Não Enviado',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id)
);

CREATE TABLE status_corte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT,
    estrutura_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    cobertura_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    acabamentos_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    internos_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    embasamento_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id)
);

CREATE TABLE status_montagem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT,
    estrutura_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    cobertura_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    acabamentos_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    internos_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    lazer_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    mobiliario_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    arborismo_status ENUM('Não Iniciado', 'Em Execução', 'Concluído') DEFAULT 'Não Iniciado',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id)
); 