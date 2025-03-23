<?php
require_once '../config/database.php';

try {
    // Verificar se a coluna já existe
    $stmt = $conn->prepare("SHOW COLUMNS FROM projetos LIKE 'status'");
    $stmt->execute();
    $coluna_existe = $stmt->rowCount() > 0;

    if (!$coluna_existe) {
        // Adicionar a coluna status
        $conn->exec("ALTER TABLE projetos ADD COLUMN status VARCHAR(50) DEFAULT 'Em dia'");
        echo "Coluna 'status' adicionada com sucesso à tabela projetos!";
    } else {
        echo "A coluna 'status' já existe na tabela projetos.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
