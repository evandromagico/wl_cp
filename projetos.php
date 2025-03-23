<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

// Definir a variável $title antes de incluir o layout
$title = "Gerenciar Projetos";

// Processar formulário de cadastro/edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nome = sanitizar($_POST['nome']);
    $data_entrega = sanitizar($_POST['data_entrega']);

    try {
        if ($id) {
            // Atualização
            $status = calcularStatusProjeto($data_entrega);
            $stmt = $conn->prepare("UPDATE projetos SET nome = ?, data_entrega = ?, status = ? WHERE id = ?");
            $stmt->execute([$nome, $data_entrega, $status, $id]);
            $mensagem = "Projeto atualizado com sucesso!";
        } else {
            // Inserção
            $status = calcularStatusProjeto($data_entrega);
            $stmt = $conn->prepare("INSERT INTO projetos (nome, data_entrega, status) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $data_entrega, $status]);
            $id = $conn->lastInsertId();
            $mensagem = "Projeto cadastrado com sucesso!";
        }

        // Limpar equipe existente se estiver atualizando
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM equipe_projeto WHERE projeto_id = ?");
            $stmt->execute([$id]);
        }

        // Inserir equipe
        if (isset($_POST['desenho_torre']) && !empty($_POST['desenho_torre'])) {
            $stmt = $conn->prepare("INSERT INTO equipe_projeto (projeto_id, usuario_id, tipo_trabalho) VALUES (?, ?, 'desenho_torre')");
            $stmt->execute([$id, $_POST['desenho_torre']]);
        }
        if (isset($_POST['desenho_embasamento']) && !empty($_POST['desenho_embasamento'])) {
            $stmt = $conn->prepare("INSERT INTO equipe_projeto (projeto_id, usuario_id, tipo_trabalho) VALUES (?, ?, 'desenho_embasamento')");
            $stmt->execute([$id, $_POST['desenho_embasamento']]);
        }
        if (isset($_POST['montagem_torre']) && !empty($_POST['montagem_torre'])) {
            $stmt = $conn->prepare("INSERT INTO equipe_projeto (projeto_id, usuario_id, tipo_trabalho) VALUES (?, ?, 'montagem_torre')");
            $stmt->execute([$id, $_POST['montagem_torre']]);
        }
        if (isset($_POST['montagem_embasamento']) && !empty($_POST['montagem_embasamento'])) {
            $stmt = $conn->prepare("INSERT INTO equipe_projeto (projeto_id, usuario_id, tipo_trabalho) VALUES (?, ?, 'montagem_embasamento')");
            $stmt->execute([$id, $_POST['montagem_embasamento']]);
        }

        $alertTipo = "success";
    } catch (PDOException $e) {
        $mensagem = "Erro ao processar: " . $e->getMessage();
        $alertTipo = "danger";
    }
}

// Buscar projetistas e montadores
$stmt = $conn->prepare("SELECT id, nome, tipo FROM usuarios WHERE tipo IN ('projetista', 'montador') ORDER BY nome");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$projetistas = array_filter($usuarios, function ($u) {
    return $u['tipo'] == 'projetista';
});
$montadores = array_filter($usuarios, function ($u) {
    return $u['tipo'] == 'montador';
});

// Buscar projetos
$stmt = $conn->prepare("
    SELECT p.id, p.nome, p.data_entrega, p.status, p.created_at,
           dt.usuario_id as desenho_torre_id,
           de.usuario_id as desenho_embasamento_id,
           mt.usuario_id as montagem_torre_id,
           me.usuario_id as montagem_embasamento_id
    FROM projetos p
    LEFT JOIN equipe_projeto dt ON p.id = dt.projeto_id AND dt.tipo_trabalho = 'desenho_torre'
    LEFT JOIN equipe_projeto de ON p.id = de.projeto_id AND de.tipo_trabalho = 'desenho_embasamento'
    LEFT JOIN equipe_projeto mt ON p.id = mt.projeto_id AND mt.tipo_trabalho = 'montagem_torre'
    LEFT JOIN equipe_projeto me ON p.id = me.projeto_id AND me.tipo_trabalho = 'montagem_embasamento'
    ORDER BY p.data_entrega
");
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerar o conteúdo da página
$content = '
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Gerenciar Projetos</h2>
        ' . (isset($mensagem) ? gerarAlerta($alertTipo, $mensagem) : '') . '
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Novo Projeto</h5>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="id" id="projetoId">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome do Projeto</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_entrega" class="form-label">Data de Entrega</label>
                        <input type="date" class="form-control" id="data_entrega" name="data_entrega" required>
                    </div>
                    <div class="col-md-3">
                        <label for="desenho_torre" class="form-label">Desenhista - Torre</label>
                        <select class="form-select" id="desenho_torre" name="desenho_torre">
                            <option value="">Selecione...</option>';

foreach ($projetistas as $projetista) {
    $content .= "<option value='{$projetista['id']}'>{$projetista['nome']}</option>";
}

$content .= '
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="desenho_embasamento" class="form-label">Desenhista - Embasamento</label>
                        <select class="form-select" id="desenho_embasamento" name="desenho_embasamento">
                            <option value="">Selecione...</option>';

foreach ($projetistas as $projetista) {
    $content .= "<option value='{$projetista['id']}'>{$projetista['nome']}</option>";
}

$content .= '
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="montagem_torre" class="form-label">Montador - Torre</label>
                        <select class="form-select" id="montagem_torre" name="montagem_torre">
                            <option value="">Selecione...</option>';

foreach ($montadores as $montador) {
    $content .= "<option value='{$montador['id']}'>{$montador['nome']}</option>";
}

$content .= '
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="montagem_embasamento" class="form-label">Montador - Embasamento</label>
                        <select class="form-select" id="montagem_embasamento" name="montagem_embasamento">
                            <option value="">Selecione...</option>';

foreach ($montadores as $montador) {
    $content .= "<option value='{$montador['id']}'>{$montador['nome']}</option>";
}

$content .= '
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="reset" class="btn btn-secondary" onclick="limparFormulario()">Novo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Projetos Cadastrados</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data de Entrega</th>
                                <th>Status</th>
                                <th>Desenhista Torre</th>
                                <th>Desenhista Embasamento</th>
                                <th>Montador Torre</th>
                                <th>Montador Embasamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>';

foreach ($projetos as $projeto) {
    $statusClass = getStatusColor($projeto['status']);

    $content .= "
        <tr>
            <td>{$projeto['nome']}</td>
            <td>" . formatarData($projeto['data_entrega']) . "</td>
            <td><span class='badge bg-{$statusClass}'>{$projeto['status']}</span></td>
            <td>" . (isset($projeto['desenho_torre_id']) ? array_values(array_filter($projetistas, function ($u) use ($projeto) {
        return $u['id'] == $projeto['desenho_torre_id'];
    }))[0]['nome'] : '-') . "</td>
            <td>" . (isset($projeto['desenho_embasamento_id']) ? array_values(array_filter($projetistas, function ($u) use ($projeto) {
        return $u['id'] == $projeto['desenho_embasamento_id'];
    }))[0]['nome'] : '-') . "</td>
            <td>" . (isset($projeto['montagem_torre_id']) ? array_values(array_filter($montadores, function ($u) use ($projeto) {
        return $u['id'] == $projeto['montagem_torre_id'];
    }))[0]['nome'] : '-') . "</td>
            <td>" . (isset($projeto['montagem_embasamento_id']) ? array_values(array_filter($montadores, function ($u) use ($projeto) {
        return $u['id'] == $projeto['montagem_embasamento_id'];
    }))[0]['nome'] : '-') . "</td>
            <td>
                <button class=\"btn btn-sm btn-primary\" onclick=\"editarProjeto(" . json_encode($projeto) . ")\">
                    <i class=\"bi bi-pencil\"></i>
                </button>
                <button class=\"btn btn-sm btn-danger\" onclick=\"excluirProjeto({$projeto['id']})\">
                    <i class=\"bi bi-trash\"></i>
                </button>
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
function editarProjeto(projeto) {
    document.getElementById("projetoId").value = projeto.id;
    document.getElementById("nome").value = projeto.nome;
    document.getElementById("data_entrega").value = projeto.data_entrega;
    document.getElementById("desenho_torre").value = projeto.desenho_torre_id || "";
    document.getElementById("desenho_embasamento").value = projeto.desenho_embasamento_id || "";
    document.getElementById("montagem_torre").value = projeto.montagem_torre_id || "";
    document.getElementById("montagem_embasamento").value = projeto.montagem_embasamento_id || "";
}

function limparFormulario() {
    document.getElementById("projetoId").value = "";
    document.getElementById("nome").value = "";
    document.getElementById("data_entrega").value = "";
    document.getElementById("desenho_torre").value = "";
    document.getElementById("desenho_embasamento").value = "";
    document.getElementById("montagem_torre").value = "";
    document.getElementById("montagem_embasamento").value = "";
}

function excluirProjeto(id) {
    if (confirm("Tem certeza que deseja excluir este projeto?")) {
        fetch("delete_projeto.php?id=" + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Projeto excluído com sucesso!");
                    location.reload();
                } else {
                    alert("Erro ao excluir projeto: " + data.message);
                }
            })
            .catch(error => {
                alert("Erro ao excluir projeto: " + error);
            });
    }
}
</script>';

// Incluir o layout após definir o conteúdo
include 'includes/layout.php';
