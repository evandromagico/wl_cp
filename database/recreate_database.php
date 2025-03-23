<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Primeiro, conectar sem selecionar um banco de dados
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Desabilitar verificação de chaves estrangeiras
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Dropar o banco de dados se existir e criar novamente
    $conn->exec("DROP DATABASE IF EXISTS maquetaria_db");
    $conn->exec("CREATE DATABASE maquetaria_db");
    $conn->exec("USE maquetaria_db");

    // Criar tabela de usuários
    $conn->exec("CREATE TABLE usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('admin', 'projetista', 'montador', 'usuario') NOT NULL DEFAULT 'usuario',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Criar tabela de projetos
    $conn->exec("CREATE TABLE projetos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        data_entrega DATE NOT NULL,
        status VARCHAR(50) DEFAULT 'Em dia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Criar tabela de equipe_projeto
    $conn->exec("CREATE TABLE equipe_projeto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        usuario_id INT NOT NULL,
        tipo_trabalho VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    // Criar tabela de status_desenho
    $conn->exec("CREATE TABLE status_desenho (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        torre_status ENUM('Não Enviado', 'Enviado', 'Em Revisão', 'Concluído') DEFAULT 'Não Enviado',
        embasamento_status ENUM('Não Enviado', 'Enviado', 'Em Revisão', 'Concluído') DEFAULT 'Não Enviado',
        internos_torre_status ENUM('Não Enviado', 'Enviado', 'Concluído') DEFAULT 'Não Enviado',
        internos_embasamento_status ENUM('Não Enviado', 'Enviado', 'Concluído') DEFAULT 'Não Enviado',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Criar tabela de status_corte
    $conn->exec("CREATE TABLE status_corte (
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
    $conn->exec("CREATE TABLE status_montagem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        estrutura INT NOT NULL DEFAULT 0,
        cobertura INT NOT NULL DEFAULT 0,
        acabamentos INT NOT NULL DEFAULT 0,
        internos INT NOT NULL DEFAULT 0,
        lazer INT NOT NULL DEFAULT 0,
        mobiliario INT NOT NULL DEFAULT 0,
        arborismo INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (projeto_id),
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Criar trigger para inserir status automaticamente quando um projeto é criado
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

    // Reabilitar verificação de chaves estrangeiras
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Criar usuário admin padrão (senha: admin123)
    $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->exec("INSERT INTO usuarios (nome, email, senha, tipo) VALUES ('Administrador', 'admin@admin.com', '$senha_hash', 'admin')");

    echo "Banco de dados e tabelas recriados com sucesso!<br>";
    echo "Um usuário administrador foi criado com:<br>";
    echo "Email: admin@admin.com<br>";
    echo "Senha: admin123<br><br>";
    echo "Você pode voltar para o <a href='../login.php'>Login</a>";
} catch (PDOException $e) {
    echo "Erro ao recriar banco de dados e tabelas: " . $e->getMessage();
}
