<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

// Verificar se é administrador
if ($_SESSION['usuario_tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit();
}

$id = intval($_GET['id']);

// Não permitir excluir o próprio usuário
if ($id === $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode excluir seu próprio usuário']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
