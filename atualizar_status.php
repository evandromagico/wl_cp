<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

header('Content-Type: application/json');

if (!isset($_POST['tipo']) || !isset($_POST['campo']) || !isset($_POST['valor']) || !isset($_POST['projeto_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$tipo = $_POST['tipo'];
$campo = $_POST['campo'];
$valor = $_POST['valor'];
$projeto_id = $_POST['projeto_id'];

try {
    switch ($tipo) {
        case 'desenho':
            $tabela = 'status_desenho';
            $campo_status = $campo . '_status';
            $sql = "UPDATE $tabela SET $campo_status = :valor WHERE projeto_id = :projeto_id";
            break;

        case 'corte':
            $tabela = 'status_corte';
            $campo_status = $campo . '_status';
            $sql = "UPDATE $tabela SET $campo_status = :valor WHERE projeto_id = :projeto_id";
            break;

        case 'montagem':
            $tabela = 'status_montagem';
            $sql = "UPDATE $tabela SET $campo = :valor WHERE projeto_id = :projeto_id";
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
            exit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':projeto_id', $projeto_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
