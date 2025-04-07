<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

header('Content-Type: application/json');

if (!isset($_POST['tipo']) || !isset($_POST['valor']) || !isset($_POST['projeto_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$tipo = $_POST['tipo'];
$valor = $_POST['valor'] ?: null;
$projeto_id = $_POST['projeto_id'];

try {
    $campo = $tipo . '_id';
    $sql = "UPDATE projetos SET $campo = :valor WHERE id = :projeto_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':projeto_id', $projeto_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar responsável']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
