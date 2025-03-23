<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];
$usuario_tipo = $_SESSION['usuario_tipo'];

try {
    // Buscar projetos com seus status
    $query = "SELECT 
        p.*,
        sd.torre_status, sd.embasamento_status, sd.internos_torre_status, sd.internos_embasamento_status,
        sc.estrutura_status, sc.cobertura_status, sc.acabamentos_status, sc.internos_status, 
        sc.embasamento_status as corte_embasamento_status, sc.lazer_status, sc.mobiliario_status, sc.arborismo_status,
        sm.estrutura, sm.cobertura, sm.acabamentos, sm.internos, sm.lazer, sm.mobiliario, sm.arborismo
    FROM projetos p
    LEFT JOIN status_desenho sd ON p.id = sd.projeto_id
    LEFT JOIN status_corte sc ON p.id = sc.projeto_id
    LEFT JOIN status_montagem sm ON p.id = sm.projeto_id
    ORDER BY p.data_entrega";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar projetos: " . $e->getMessage());
}

// Função para calcular progresso do desenho
function calcularProgressoDesenho($projeto)
{
    $status_map = [
        'Não Iniciado' => 0,
        'Em Andamento' => 50,
        'Enviado' => 75,
        'Não Enviado' => 0,
        'Em Revisão' => 60,
        'Concluído' => 100
    ];

    $total = 0;
    $total += $status_map[$projeto['torre_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['embasamento_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['internos_torre_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['internos_embasamento_status'] ?? 'Não Iniciado'];

    return round($total / 4);
}

// Função para calcular progresso do corte
function calcularProgressoCorte($projeto)
{
    $status_map = [
        'Não Iniciado' => 0,
        'Em Andamento' => 50,
        'Concluído' => 100
    ];

    $total = 0;
    $total += $status_map[$projeto['estrutura_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['cobertura_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['acabamentos_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['internos_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['corte_embasamento_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['lazer_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['mobiliario_status'] ?? 'Não Iniciado'];
    $total += $status_map[$projeto['arborismo_status'] ?? 'Não Iniciado'];

    return round($total / 8);
}

// Função para calcular progresso da montagem
function calcularProgressoMontagem($projeto)
{
    $status_map = [
        0 => 0,    // Não Iniciado
        1 => 50,   // Em Execução
        2 => 100   // Concluído
    ];

    $total = 0;
    $total += $status_map[$projeto['estrutura'] ?? 0];
    $total += $status_map[$projeto['cobertura'] ?? 0];
    $total += $status_map[$projeto['acabamentos'] ?? 0];
    $total += $status_map[$projeto['internos'] ?? 0];
    $total += $status_map[$projeto['lazer'] ?? 0];
    $total += $status_map[$projeto['mobiliario'] ?? 0];
    $total += $status_map[$projeto['arborismo'] ?? 0];

    return round($total / 7);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gerenciamento de Maquetes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <?php if ($usuario_tipo == 'admin'): ?>
                <a href="projetos.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Projeto
                </a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome do Projeto</th>
                        <th>Data de Entrega</th>
                        <th>Status</th>
                        <th>Desenho</th>
                        <th>Corte</th>
                        <th>Montagem</th>
                        <th>Progresso Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projetos as $projeto): ?>
                        <?php
                        $progresso_desenho = calcularProgressoDesenho($projeto);
                        $progresso_corte = calcularProgressoCorte($projeto);
                        $progresso_montagem = calcularProgressoMontagem($projeto);
                        $progresso_total = round(($progresso_desenho + $progresso_corte + $progresso_montagem) / 3);
                        $data_entrega = formatarData($projeto['data_entrega']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($projeto['nome']); ?></td>
                            <td><?php echo $data_entrega; ?></td>
                            <td><?php echo htmlspecialchars($projeto['status']); ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progresso_desenho; ?>%"
                                        aria-valuenow="<?php echo $progresso_desenho; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $progresso_desenho; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progresso_corte; ?>%"
                                        aria-valuenow="<?php echo $progresso_corte; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $progresso_corte; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progresso_montagem; ?>%"
                                        aria-valuenow="<?php echo $progresso_montagem; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $progresso_montagem; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progresso_total; ?>%"
                                        aria-valuenow="<?php echo $progresso_total; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $progresso_total; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="status_desenho.php?id=<?php echo $projeto['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Desenho
                                    </a>
                                    <a href="status_corte.php?id=<?php echo $projeto['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-scissors"></i> Corte
                                    </a>
                                    <a href="status_montagem.php?id=<?php echo $projeto['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-tools"></i> Montagem
                                    </a>
                                    <?php if ($usuario_tipo == 'admin'): ?>
                                        <button onclick="confirmarExclusao(<?php echo $projeto['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este projeto?')) {
                fetch('delete_projeto.php?id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Projeto excluído com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro ao excluir projeto: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Erro ao excluir projeto: ' + error);
                    });
            }
        }
    </script>
</body>

</html>