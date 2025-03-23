<?php
require_once '../config/database.php';

try {
    $conn->exec("ALTER TABLE projetos DROP COLUMN IF EXISTS status");
    echo "Coluna 'status' removida com sucesso da tabela projetos!";
} catch (PDOException $e) {
    echo "Erro ao remover coluna: " . $e->getMessage();
}
