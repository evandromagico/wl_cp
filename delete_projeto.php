<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $conn->beginTransaction();

        // Excluir registros relacionados
        $conn->prepare("DELETE FROM status_montagem WHERE projeto_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM status_corte WHERE projeto_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM status_desenho WHERE projeto_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM equipe_projeto WHERE projeto_id = ?")->execute([$id]);

        // Excluir projeto
        $conn->prepare("DELETE FROM projetos WHERE id = ?")->execute([$id]);

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Requisição inválida'
    ]);
}
