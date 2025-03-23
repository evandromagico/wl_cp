<?php
require_once 'config/database.php';

try {
    // Primeiro, vamos criar o banco de dados se ele não existir
    $conn->exec("CREATE DATABASE IF NOT EXISTS maquetaria_db");
    $conn->exec("USE maquetaria_db");

    // Agora vamos criar a tabela de usuários se ela não existir
    $conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario'
    )");

    // Agora vamos inserir o usuário administrador
    $nome = 'Administrador';
    $email = 'admin@admin.com';
    $senha = password_hash('admin123', PASSWORD_DEFAULT);
    $tipo = 'admin';

    // Primeiro vamos verificar se o usuário já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if (!$stmt->fetch()) {
        // Se o usuário não existe, vamos criar
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha, $tipo]);
        echo "Usuário administrador criado com sucesso!<br>";
    } else {
        // Se o usuário já existe, vamos atualizar a senha
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$senha, $email]);
        echo "Senha do usuário administrador atualizada com sucesso!<br>";
    }

    echo "Email: admin@admin.com<br>";
    echo "Senha: admin123";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
