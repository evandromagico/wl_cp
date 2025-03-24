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
        $stmt = $conn->prepare("UPDATE status_corte SET {$campo} = ? WHERE projeto_id = ?");
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
    SELECT p.*, sc.*
    FROM projetos p
    LEFT JOIN status_corte sc ON p.id = sc.projeto_id
    ORDER BY p.data_entrega
");
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Status de Corte</h2>
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
                                <th>Estrutura</th>
                                <th>Cobertura</th>
                                <th>Acabamentos</th>
                                <th>Internos</th>
                                <th>Embasamento</th>
                                <th>Lazer</th>
                                <th>Mobiliário</th>
                                <th>Arborismo</th>
                            </tr>
                        </thead>
                        <tbody>';

foreach ($projetos as $projeto) {
    $progresso_desenho = calcularProgressoDesenho($projeto);
    $progresso_corte = calcularProgressoCorte($projeto);
    $progresso_montagem = calcularProgressoMontagem($projeto);
    $progresso_total = round(($progresso_desenho + $progresso_corte + $progresso_montagem) / 3);
    $status = calcularStatusProjeto($projeto['data_entrega']);
    $statusClass = getStatusColor($projeto['data_entrega'], $progresso_total);

    $content .= "
        <tr>
            <td>{$projeto['nome']}</td>
            <td>" . formatarData($projeto['data_entrega']) . "</td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='estrutura_status'>
                    <option value='Não Iniciado'" . ($projeto['estrutura_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['estrutura_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['estrutura_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='cobertura_status'>
                    <option value='Não Iniciado'" . ($projeto['cobertura_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['cobertura_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['cobertura_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='acabamentos_status'>
                    <option value='Não Iniciado'" . ($projeto['acabamentos_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['acabamentos_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['acabamentos_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='internos_status'>
                    <option value='Não Iniciado'" . ($projeto['internos_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['internos_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['internos_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='embasamento_status'>
                    <option value='Não Iniciado'" . ($projeto['embasamento_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['embasamento_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['embasamento_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='lazer_status'>
                    <option value='Não Iniciado'" . ($projeto['lazer_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['lazer_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['lazer_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='mobiliario_status'>
                    <option value='Não Iniciado'" . ($projeto['mobiliario_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['mobiliario_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['mobiliario_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
                </select>
            </td>
            <td>
                <select class='form-select form-select-sm status-select' 
                        data-projeto-id='{$projeto['id']}' 
                        data-campo='arborismo_status'>
                    <option value='Não Iniciado'" . ($projeto['arborismo_status'] == 'Não Iniciado' ? ' selected' : '') . ">Não Iniciado</option>
                    <option value='Em Andamento'" . ($projeto['arborismo_status'] == 'Em Andamento' ? ' selected' : '') . ">Em Andamento</option>
                    <option value='Concluído'" . ($projeto['arborismo_status'] == 'Concluído' ? ' selected' : '') . ">Concluído</option>
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
        
        fetch("status_corte.php", {
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
