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
        $stmt = $conn->prepare("UPDATE status_montagem SET {$campo} = ? WHERE projeto_id = ?");
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
    SELECT p.*, sm.*,
           mt.usuario_id as montagem_torre_id, u1.nome as montagem_torre_nome,
           me.usuario_id as montagem_embasamento_id, u2.nome as montagem_embasamento_nome
    FROM projetos p
    LEFT JOIN status_montagem sm ON p.id = sm.projeto_id
    LEFT JOIN equipe_projeto mt ON p.id = mt.projeto_id AND mt.tipo_trabalho = 'montagem_torre'
    LEFT JOIN equipe_projeto me ON p.id = me.projeto_id AND me.tipo_trabalho = 'montagem_embasamento'
    LEFT JOIN usuarios u1 ON mt.usuario_id = u1.id
    LEFT JOIN usuarios u2 ON me.usuario_id = u2.id
    ORDER BY p.data_entrega
");
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Status de Montagem</h2>
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
                                <th>Status</th>
                                <th>Montador Torre</th>
                                <th>Montador Embasamento</th>
                                <th>Estrutura</th>
                                <th>Cobertura</th>
                                <th>Acabamentos</th>
                                <th>Internos</th>
                                <th>Lazer</th>
                                <th>Mobiliário</th>
                                <th>Arborismo</th>
                            </tr>
                        </thead>
                        <tbody>';

foreach ($projetos as $projeto) {
    $status = calcularStatusProjeto($projeto['data_entrega']);
    $statusClass = getStatusColor($status);

    $content .= "
        <tr>
            <td>{$projeto['nome']}</td>
            <td>" . formatarData($projeto['data_entrega']) . "</td>
            <td><span class='badge bg-{$statusClass}'>{$status}</span></td>
            <td>{$projeto['montagem_torre_nome']}</td>
            <td>{$projeto['montagem_embasamento_nome']}</td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='estrutura'>
                    <option value='0'" . ($projeto['estrutura'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['estrutura'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['estrutura'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='cobertura'>
                    <option value='0'" . ($projeto['cobertura'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['cobertura'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['cobertura'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='acabamentos'>
                    <option value='0'" . ($projeto['acabamentos'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['acabamentos'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['acabamentos'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='internos'>
                    <option value='0'" . ($projeto['internos'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['internos'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['internos'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='lazer'>
                    <option value='0'" . ($projeto['lazer'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['lazer'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['lazer'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='mobiliario'>
                    <option value='0'" . ($projeto['mobiliario'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['mobiliario'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['mobiliario'] == 2 ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='arborismo'>
                    <option value='0'" . ($projeto['arborismo'] == 0 ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='1'" . ($projeto['arborismo'] == 1 ? ' selected' : '') . ">Em Execução</option>
                    <option value='2'" . ($projeto['arborismo'] == 2 ? ' selected' : '') . ">Concluído</option>
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
        
        fetch("status_montagem.php", {
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
