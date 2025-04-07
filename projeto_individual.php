<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$projeto_id = $_GET['id'];

try {
    // Buscar dados do projeto específico com projetista e montador
    $query = "SELECT 
        p.*,
        sd.torre_status, sd.embasamento_status, sd.internos_torre_status, sd.internos_embasamento_status,
        sc.estrutura_status, sc.cobertura_status, sc.acabamentos_status, sc.internos_status, 
        sc.embasamento_status as corte_embasamento_status, sc.lazer_status, sc.mobiliario_status, sc.arborismo_status,
        sm.estrutura, sm.cobertura, sm.acabamentos, sm.internos, sm.lazer, sm.mobiliario, sm.arborismo,
        u1.nome as projetista_nome,
        u2.nome as montador_nome,
        u1.id as projetista_id,
        u2.id as montador_id
    FROM projetos p
    LEFT JOIN status_desenho sd ON p.id = sd.projeto_id
    LEFT JOIN status_corte sc ON p.id = sc.projeto_id
    LEFT JOIN status_montagem sm ON p.id = sm.projeto_id
    LEFT JOIN usuarios u1 ON p.projetista_id = u1.id
    LEFT JOIN usuarios u2 ON p.montador_id = u2.id
    WHERE p.id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $projeto_id);
    $stmt->execute();
    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        header('Location: dashboard.php');
        exit();
    }

    // Buscar todos os usuários para os selects de projetista e montador
    $query_usuarios = "SELECT id, nome FROM usuarios ORDER BY nome";
    $stmt_usuarios = $conn->prepare($query_usuarios);
    $stmt_usuarios->execute();
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar projeto: " . $e->getMessage());
}

$progresso_desenho = calcularProgressoDesenho($projeto);
$progresso_corte = calcularProgressoCorte($projeto);
$progresso_montagem = calcularProgressoMontagem($projeto);
$progresso_total = round(($progresso_desenho + $progresso_corte + $progresso_montagem) / 3);

$content = '
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>' . htmlspecialchars($projeto['nome']) . '</h2>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title">Progresso Total</h5>
                        <div class="progress-circle" data-progress="' . $progresso_total . '">
                            <svg class="progress-circle-svg" viewBox="0 0 100 100">
                                <circle class="progress-circle-bg" cx="50" cy="50" r="45"/>
                                <circle class="progress-circle-fill" cx="50" cy="50" r="45"/>
                            </svg>
                            <div class="progress-circle-text">' . $progresso_total . '%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Detalhes do Projeto</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Data de Entrega:</strong> ' . formatarData($projeto['data_entrega']) . '</p>
                                <p><strong>Status:</strong> <span class="badge bg-' . getStatusColor($projeto['data_entrega'], $progresso_total)['color'] . '">' . getStatusColor($projeto['data_entrega'], $progresso_total)['text'] . '</span></p>
                                <div class="form-group mb-3">
                                    <label><strong>Projetista:</strong></label>
                                    <select class="form-select" onchange="atualizarResponsavel(\'projetista\', this.value, ' . $projeto_id . ')">
                                        <option value="">Selecione um projetista</option>';
foreach ($usuarios as $usuario) {
    $content .= '<option value="' . $usuario['id'] . '" ' . ($usuario['id'] == $projeto['projetista_id'] ? 'selected' : '') . '>' . htmlspecialchars($usuario['nome']) . '</option>';
}
$content .= '
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label><strong>Montador:</strong></label>
                                    <select class="form-select" onchange="atualizarResponsavel(\'montador\', this.value, ' . $projeto_id . ')">
                                        <option value="">Selecione um montador</option>';
foreach ($usuarios as $usuario) {
    $content .= '<option value="' . $usuario['id'] . '" ' . ($usuario['id'] == $projeto['montador_id'] ? 'selected' : '') . '>' . htmlspecialchars($usuario['nome']) . '</option>';
}
$content .= '
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Progresso Desenho:</strong> ' . $progresso_desenho . '%</p>
                                <p><strong>Progresso Corte:</strong> ' . $progresso_corte . '%</p>
                                <p><strong>Progresso Montagem:</strong> ' . $progresso_montagem . '%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Desenho</h5>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: ' . $progresso_desenho . '%">
                                ' . $progresso_desenho . '%
                            </div>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-pencil"></i> Torre</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'desenho\', \'torre\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_enviado" ' . ($projeto['torre_status'] == 'nao_enviado' ? 'selected' : '') . '>Não Enviado</option>
                                        <option value="em_revisao" ' . ($projeto['torre_status'] == 'em_revisao' ? 'selected' : '') . '>Em Revisão</option>
                                        <option value="enviado" ' . ($projeto['torre_status'] == 'enviado' ? 'selected' : '') . '>Enviado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-pencil"></i> Embasamento</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'desenho\', \'embasamento\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_enviado" ' . ($projeto['embasamento_status'] == 'nao_enviado' ? 'selected' : '') . '>Não Enviado</option>
                                        <option value="em_revisao" ' . ($projeto['embasamento_status'] == 'em_revisao' ? 'selected' : '') . '>Em Revisão</option>
                                        <option value="enviado" ' . ($projeto['embasamento_status'] == 'enviado' ? 'selected' : '') . '>Enviado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-pencil"></i> Internos Torre</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'desenho\', \'internos_torre\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_enviado" ' . ($projeto['internos_torre_status'] == 'nao_enviado' ? 'selected' : '') . '>Não Enviado</option>
                                        <option value="em_revisao" ' . ($projeto['internos_torre_status'] == 'em_revisao' ? 'selected' : '') . '>Em Revisão</option>
                                        <option value="enviado" ' . ($projeto['internos_torre_status'] == 'enviado' ? 'selected' : '') . '>Enviado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-pencil"></i> Internos Embasamento</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'desenho\', \'internos_embasamento\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_enviado" ' . ($projeto['internos_embasamento_status'] == 'nao_enviado' ? 'selected' : '') . '>Não Enviado</option>
                                        <option value="em_revisao" ' . ($projeto['internos_embasamento_status'] == 'em_revisao' ? 'selected' : '') . '>Em Revisão</option>
                                        <option value="enviado" ' . ($projeto['internos_embasamento_status'] == 'enviado' ? 'selected' : '') . '>Enviado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Corte</h5>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: ' . $progresso_corte . '%">
                                ' . $progresso_corte . '%
                            </div>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Estrutura</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'estrutura\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['estrutura_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['estrutura_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['estrutura_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Cobertura</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'cobertura\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['cobertura_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['cobertura_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['cobertura_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Acabamentos</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'acabamentos\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['acabamentos_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['acabamentos_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['acabamentos_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Internos</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'internos\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['internos_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['internos_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['internos_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Embasamento</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'embasamento\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['corte_embasamento_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['corte_embasamento_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['corte_embasamento_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Lazer</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'lazer\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['lazer_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['lazer_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['lazer_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Mobiliário</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'mobiliario\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['mobiliario_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['mobiliario_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['mobiliario_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-scissors"></i> Arborismo</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'corte\', \'arborismo\', this.value, ' . $projeto_id . ')">
                                        <option value="nao_iniciado" ' . ($projeto['arborismo_status'] == 'nao_iniciado' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="em_andamento" ' . ($projeto['arborismo_status'] == 'em_andamento' ? 'selected' : '') . '>Em Andamento</option>
                                        <option value="concluido" ' . ($projeto['arborismo_status'] == 'concluido' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Montagem</h5>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: ' . $progresso_montagem . '%">
                                ' . $progresso_montagem . '%
                            </div>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Estrutura</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'estrutura\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['estrutura'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['estrutura'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['estrutura'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Cobertura</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'cobertura\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['cobertura'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['cobertura'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['cobertura'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Acabamentos</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'acabamentos\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['acabamentos'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['acabamentos'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['acabamentos'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Internos</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'internos\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['internos'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['internos'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['internos'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Lazer</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'lazer\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['lazer'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['lazer'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['lazer'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Mobiliário</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'mobiliario\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['mobiliario'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['mobiliario'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['mobiliario'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Arborismo</span>
                                    <select class="form-select form-select-sm w-50" onchange="atualizarStatus(\'montagem\', \'arborismo\', this.value, ' . $projeto_id . ')">
                                        <option value="0" ' . ($projeto['arborismo'] == '0' ? 'selected' : '') . '>Não Iniciado</option>
                                        <option value="1" ' . ($projeto['arborismo'] == '1' ? 'selected' : '') . '>Em Execução</option>
                                        <option value="2" ' . ($projeto['arborismo'] == '2' ? 'selected' : '') . '>Concluído</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function atualizarStatus(tipo, campo, valor, projetoId) {
        fetch(`atualizar_status.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `tipo=${tipo}&campo=${campo}&valor=${valor}&projeto_id=${projetoId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Erro ao atualizar status: " + data.message);
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            alert("Erro ao atualizar status");
        });
    }

    function atualizarResponsavel(tipo, valor, projetoId) {
        fetch(`atualizar_responsavel.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `tipo=${tipo}&valor=${valor}&projeto_id=${projetoId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Erro ao atualizar responsável: " + data.message);
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            alert("Erro ao atualizar responsável");
        });
    }
    </script>

    <style>
        .progress-circle {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .progress-circle-svg {
            transform: rotate(-90deg);
        }
        .progress-circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 8;
        }
        .progress-circle-fill {
            fill: none;
            stroke: #2196F3;
            stroke-width: 8;
            stroke-dasharray: 283;
            stroke-dashoffset: calc(283 - (283 * ' . $progresso_total . ') / 100);
            transition: stroke-dashoffset 0.3s ease;
        }
        .progress-circle-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
        }
        .form-select-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }
    </style>';

require_once 'includes/layout.php';
