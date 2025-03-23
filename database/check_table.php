<?php
require_once '../config/database.php';

try {
    $stmt = $conn->prepare("SHOW COLUMNS FROM projetos");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
