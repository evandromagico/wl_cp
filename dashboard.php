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

$content = '
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            ' . ($usuario_tipo == 'admin' ? '
                <a href="projetos.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Projeto
                </a>
            ' : '') . '
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
                <tbody>';

foreach ($projetos as $projeto) {
    $progresso_desenho = calcularProgressoDesenho($projeto);
    $progresso_corte = calcularProgressoCorte($projeto);
    $progresso_montagem = calcularProgressoMontagem($projeto);
    $progresso_total = round(($progresso_desenho + $progresso_corte + $progresso_montagem) / 3);
    $data_entrega = formatarData($projeto['data_entrega']);
    $status = getStatusColor($projeto['data_entrega'], $progresso_total);

    $content .= "
        <tr>
            <td><a href='projeto_individual.php?id={$projeto['id']}' class='text-decoration-none'>" . htmlspecialchars($projeto['nome']) . "</a></td>
            <td>{$data_entrega}</td>
            <td><span class='badge bg-{$status['color']}'>{$status['text']}</span></td>
            <td>
                <div class='progress'>
                    <div class='progress-bar' role='progressbar' style='width: {$progresso_desenho}%'
                        aria-valuenow='{$progresso_desenho}' aria-valuemin='0' aria-valuemax='100'>
                        {$progresso_desenho}%
                    </div>
                </div>
            </td>
            <td>
                <div class='progress'>
                    <div class='progress-bar' role='progressbar' style='width: {$progresso_corte}%'
                        aria-valuenow='{$progresso_corte}' aria-valuemin='0' aria-valuemax='100'>
                        {$progresso_corte}%
                    </div>
                </div>
            </td>
            <td>
                <div class='progress'>
                    <div class='progress-bar' role='progressbar' style='width: {$progresso_montagem}%'
                        aria-valuenow='{$progresso_montagem}' aria-valuemin='0' aria-valuemax='100'>
                        {$progresso_montagem}%
                    </div>
                </div>
            </td>
            <td>
                <a href='projeto_individual.php?id={$projeto['id']}' class='text-decoration-none'>
                    <div class='progress'>
                        <div class='progress-bar' role='progressbar' style='width: {$progresso_total}%'
                            aria-valuenow='{$progresso_total}' aria-valuemin='0' aria-valuemax='100'>
                            {$progresso_total}%
                        </div>
                    </div>
                </a>
            </td>
            <td>
                <div class='btn-group'>
                    <a href='projeto_individual.php?id={$projeto['id']}' class='btn btn-sm btn-outline-primary'>
                        <i class='bi bi-pencil-square'></i> Modificar Status
                    </a>
                    " . ($usuario_tipo == 'admin' ? "
                        <button onclick='confirmarExclusao({$projeto['id']})' class='btn btn-sm btn-outline-danger'>
                            <i class='bi bi-trash'></i>
                        </button>
                    " : "") . "
                </div>
            </td>
        </tr>";
}

$content .= '
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Progresso das Maquetes</h5>
                    <div class="row">';

foreach ($projetos as $projeto) {
    $progresso_desenho = calcularProgressoDesenho($projeto);
    $progresso_corte = calcularProgressoCorte($projeto);
    $progresso_montagem = calcularProgressoMontagem($projeto);
    $progresso_total = round(($progresso_desenho + $progresso_corte + $progresso_montagem) / 3);

    $content .= '
        <div class="col-md-3 mb-4">
            <div class="text-center">
                <a href="projeto_individual.php?id=' . $projeto['id'] . '" class="text-decoration-none">
                    <h6>' . htmlspecialchars($projeto['nome']) . '</h6>
                </a>
                <div class="circular-progress-container">
                    <a href="projeto_individual.php?id=' . $projeto['id'] . '" class="text-decoration-none">
                        <div class="circular-progress" id="main_progress_' . $projeto['id'] . '">
                            <div class="progress-value">' . $progresso_total . '%</div>
                        </div>
                    </a>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <div class="progress-item">
                        <a href="projeto_individual.php?id=' . $projeto['id'] . '" class="progress-link" title="Editar Desenho">
                            <div class="small-circular-progress" id="desenho_progress_' . $projeto['id'] . '">
                                <div class="small-progress-value">' . $progresso_desenho . '%</div>
                            </div>
                            <div class="progress-label">Desenho</div>
                        </a>
                    </div>
                    <div class="progress-item">
                        <a href="projeto_individual.php?id=' . $projeto['id'] . '" class="progress-link" title="Editar Corte">
                            <div class="small-circular-progress" id="corte_progress_' . $projeto['id'] . '">
                                <div class="small-progress-value">' . $progresso_corte . '%</div>
                            </div>
                            <div class="progress-label">Corte</div>
                        </a>
                    </div>
                    <div class="progress-item">
                        <a href="projeto_individual.php?id=' . $projeto['id'] . '" class="progress-link" title="Editar Montagem">
                            <div class="small-circular-progress" id="montagem_progress_' . $projeto['id'] . '">
                                <div class="small-progress-value">' . $progresso_montagem . '%</div>
                            </div>
                            <div class="progress-label">Montagem</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>';
}

$content .= '
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .circular-progress-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 15px;
        }
        
        .circular-progress {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .progress-value {
            position: absolute;
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        
        .small-circular-progress {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }
        
        .small-progress-value {
            position: absolute;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .progress-item {
            flex: 1;
            max-width: 80px;
            margin: 0 5px;
        }
        
        .progress-label {
            font-size: 12px;
            margin-top: 5px;
            text-align: center;
        }
        .progress-link {
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s;
}

.progress-link:hover {
    transform: scale(1.05);
    color: inherit;
}

.progress-link:hover .small-circular-progress {
    box-shadow: 0 0 10px rgba(30, 136, 229, 0.5);
}
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/progressbar.js/dist/progressbar.min.js"></script>
    <script>
        function confirmarExclusao(id) {
            if (confirm("Tem certeza que deseja excluir este projeto?")) {
                window.location.href = "excluir_projeto.php?id=" + id;
            }
        }

        // Dados dos projetos
        const projetos = ' . json_encode(array_map(function ($projeto) {
    return [
        'id' => $projeto['id'],
        'nome' => $projeto['nome'],
        'progresso_total' => round((calcularProgressoDesenho($projeto) + calcularProgressoCorte($projeto) + calcularProgressoMontagem($projeto)) / 3),
        'progresso_desenho' => calcularProgressoDesenho($projeto),
        'progresso_corte' => calcularProgressoCorte($projeto),
        'progresso_montagem' => calcularProgressoMontagem($projeto)
    ];
}, $projetos)) . ';

        document.addEventListener("DOMContentLoaded", function() {
            // Configuração global
            const mainColor = "#1E88E5";
            const desenhoColor = "#1E88E5";
            const corteColor = "#1E88E5";
            const montagemColor = "#1E88E5";
            
            projetos.forEach(projeto => {
                // Circular progress para o progresso total
                const mainProgress = new ProgressBar.Circle(`#main_progress_${projeto.id}`, {
                    color: mainColor,
                    strokeWidth: 10,
                    trailWidth: 10,
                    trailColor: "#F5F5F5",
                    easing: "easeInOut",
                    duration: 1400,
                    text: {
                        autoStyleContainer: false
                    },
                    from: { color: mainColor, width: 10 },
                    to: { color: mainColor, width: 10 },
                    step: function(state, circle) {
                        circle.path.setAttribute("stroke", state.color);
                        circle.path.setAttribute("stroke-width", state.width);
                    }
                });
                mainProgress.animate(projeto.progresso_total / 100);
                
                // Circular progress para o desenho
                const desenhoProgress = new ProgressBar.Circle(`#desenho_progress_${projeto.id}`, {
                    color: desenhoColor,
                    strokeWidth: 8,
                    trailWidth: 8,
                    trailColor: "#F5F5F5",
                    easing: "easeInOut",
                    duration: 1400,
                    from: { color: desenhoColor, width: 8 },
                    to: { color: desenhoColor, width: 8 },
                    step: function(state, circle) {
                        circle.path.setAttribute("stroke", state.color);
                        circle.path.setAttribute("stroke-width", state.width);
                    }
                });
                desenhoProgress.animate(projeto.progresso_desenho / 100);
                
                // Circular progress para o corte
                const corteProgress = new ProgressBar.Circle(`#corte_progress_${projeto.id}`, {
                    color: corteColor,
                    strokeWidth: 8,
                    trailWidth: 8,
                    trailColor: "#F5F5F5",
                    easing: "easeInOut",
                    duration: 1400,
                    from: { color: corteColor, width: 8 },
                    to: { color: corteColor, width: 8 },
                    step: function(state, circle) {
                        circle.path.setAttribute("stroke", state.color);
                        circle.path.setAttribute("stroke-width", state.width);
                    }
                });
                corteProgress.animate(projeto.progresso_corte / 100);
                
                // Circular progress para a montagem
                const montagemProgress = new ProgressBar.Circle(`#montagem_progress_${projeto.id}`, {
                    color: montagemColor,
                    strokeWidth: 8,
                    trailWidth: 8,
                    trailColor: "#F5F5F5",
                    easing: "easeInOut",
                    duration: 1400,
                    from: { color: montagemColor, width: 8 },
                    to: { color: montagemColor, width: 8 },
                    step: function(state, circle) {
                        circle.path.setAttribute("stroke", state.color);
                        circle.path.setAttribute("stroke-width", state.width);
                    }
                });
                montagemProgress.animate(projeto.progresso_montagem / 100);
            });
        });
    </script>';

require_once 'includes/layout.php';
