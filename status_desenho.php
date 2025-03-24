<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

// Processar atualização de status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $projeto_id = intval($_POST['projeto_id']);
    $campo = sanitizar($_POST['campo']);
    $valor = sanitizar($_POST['valor']);

    try {
        $stmt = $conn->prepare("UPDATE status_desenho SET {$campo} = ? WHERE projeto_id = ?");
        $stmt->execute([$valor, $projeto_id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Buscar projetos e seus status
$stmt = $conn->prepare("
    SELECT p.*, sd.*,
           dt.usuario_id as desenho_torre_id, u1.nome as desenho_torre_nome,
           de.usuario_id as desenho_embasamento_id, u2.nome as desenho_embasamento_nome
    FROM projetos p
    LEFT JOIN status_desenho sd ON p.id = sd.projeto_id
    LEFT JOIN equipe_projeto dt ON p.id = dt.projeto_id AND dt.tipo_trabalho = 'desenho_torre'
    LEFT JOIN equipe_projeto de ON p.id = de.projeto_id AND de.tipo_trabalho = 'desenho_embasamento'
    LEFT JOIN usuarios u1 ON dt.usuario_id = u1.id
    LEFT JOIN usuarios u2 ON de.usuario_id = u2.id
    ORDER BY p.data_entrega
");
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Status de Desenho</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Projeto</th>
                                <th>Data de Entrega</th>
                                <th>Desenhista Torre</th>
                                <th>Desenhista Embasamento</th>
                                <th>Torre</th>
                                <th>Embasamento</th>
                                <th>Internos Torre</th>
                                <th>Internos Embasamento</th>
                            </tr>
                        </thead>
                        <tbody>';

foreach ($projetos as $projeto) {
    $status = calcularStatusProjeto($projeto['data_entrega']);
    $statusClass = getStatusColor($projeto['data_entrega']);

    $content .= "
        <tr>
            <td>{$projeto['nome']}</td>
            <td>" . formatarData($projeto['data_entrega']) . "</td>
            <td>{$projeto['desenho_torre_nome']}</td>
            <td>{$projeto['desenho_embasamento_nome']}</td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='torre_status'>
                    <option value='Não Enviado'" . ($projeto['torre_status'] == 'Não Enviado' ? ' selected' : '') . ">Não Enviado</option>
                    <option value='Enviado'" . ($projeto['torre_status'] == 'Enviado' ? ' selected' : '') . ">Enviado</option>
                    <option value='Em Revisão'" . ($projeto['torre_status'] == 'Em Revisão' ? ' selected' : '') . ">Em Revisão</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='embasamento_status'>
                    <option value='Não Enviado'" . ($projeto['embasamento_status'] == 'Não Enviado' ? ' selected' : '') . ">Não Enviado</option>
                    <option value='Enviado'" . ($projeto['embasamento_status'] == 'Enviado' ? ' selected' : '') . ">Enviado</option>
                    <option value='Em Revisão'" . ($projeto['embasamento_status'] == 'Em Revisão' ? ' selected' : '') . ">Em Revisão</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='internos_torre_status'>
                    <option value='Não Enviado'" . ($projeto['internos_torre_status'] == 'Não Enviado' ? ' selected' : '') . ">Não Enviado</option>
                    <option value='Enviado'" . ($projeto['internos_torre_status'] == 'Enviado' ? ' selected' : '') . ">Enviado</option>
                    <option value='Em Revisão'" . ($projeto['internos_torre_status'] == 'Em Revisão' ? ' selected' : '') . ">Em Revisão</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='internos_embasamento_status'>
                    <option value='Não Enviado'" . ($projeto['internos_embasamento_status'] == 'Não Enviado' ? ' selected' : '') . ">Não Enviado</option>
                    <option value='Enviado'" . ($projeto['internos_embasamento_status'] == 'Enviado' ? ' selected' : '') . ">Enviado</option>
                    <option value='Em Revisão'" . ($projeto['internos_embasamento_status'] == 'Em Revisão' ? ' selected' : '') . ">Em Revisão</option>
                </select>
            </td>
        </tr>";
}

$content .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll(".status-select").forEach(select => {
    select.addEventListener("change", function() {
        const projetoId = this.dataset.projetoId;
        const campo = this.dataset.campo;
        const valor = this.value;
        
        fetch("status_desenho.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `projeto_id=${projetoId}&campo=${campo}&valor=${valor}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert("Erro ao atualizar status: " + data.message);
                location.reload();
            }
        });
    });
});
</script>';

include 'includes/layout.php';
