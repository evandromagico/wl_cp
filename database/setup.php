<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Primeiro, conectar sem selecionar um banco de dados
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar o banco de dados se não existir
    $conn->exec("CREATE DATABASE IF NOT EXISTS maquetaria_db");
    $conn->exec("USE maquetaria_db");

    // Criar tabela de projetos
    $conn->exec("CREATE TABLE IF NOT EXISTS projetos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        data_entrega DATE NOT NULL,
        status VARCHAR(50) DEFAULT 'Em dia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Criar tabela de status_desenho
    $conn->exec("CREATE TABLE IF NOT EXISTS status_desenho (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        torre_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        internos_torre_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        internos_embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Criar tabela de status_corte
    $conn->exec("CREATE TABLE IF NOT EXISTS status_corte (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        estrutura_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        cobertura_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        acabamentos_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        internos_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        lazer_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        mobiliario_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        arborismo_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Criar tabela de status_montagem
    $conn->exec("CREATE TABLE IF NOT EXISTS status_montagem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        estrutura INT NOT NULL DEFAULT 0,
        acabamentos INT NOT NULL DEFAULT 0,
        base INT NOT NULL DEFAULT 0,
        detalhes INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (projeto_id),
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Criar triggers para inserir status automaticamente quando um projeto é criado
    $conn->exec("DROP TRIGGER IF EXISTS after_projeto_insert");
    $conn->exec("
        CREATE TRIGGER after_projeto_insert 
        AFTER INSERT ON projetos
        FOR EACH ROW
        BEGIN
            INSERT INTO status_desenho (projeto_id) VALUES (NEW.id);
            INSERT INTO status_corte (projeto_id) VALUES (NEW.id);
            INSERT INTO status_montagem (projeto_id) VALUES (NEW.id);
        END
    ");

    echo "Banco de dados e tabelas criados com sucesso!<br>";
    echo "Você pode voltar para o <a href='../dashboard.php'>Dashboard</a>";
} catch (PDOException $e) {
    echo "Erro ao criar banco de dados e tabelas: " . $e->getMessage();
}
