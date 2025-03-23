<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

verificarLogin();

// Verificar se é administrador
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Processar formulário de cadastro/edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nome = sanitizar($_POST['nome']);
    $email = sanitizar($_POST['email']);
    $tipo = sanitizar($_POST['tipo']);
    $senha = isset($_POST['senha']) && !empty($_POST['senha']) ?
        password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;

    try {
        if ($id) {
            // Atualização
            if ($senha) {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, tipo = ?, senha = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $tipo, $senha, $id]);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $tipo, $id]);
            }
            $mensagem = "Usuário atualizado com sucesso!";
        } else {
            // Inserção
            if (!$senha) {
                throw new Exception("A senha é obrigatória para novos usuários");
            }
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha, $tipo]);
            $mensagem = "Usuário cadastrado com sucesso!";
        }
        $alertTipo = "success";
    } catch (Exception $e) {
        $mensagem = "Erro ao processar: " . $e->getMessage();
        $alertTipo = "danger";
    }
}

// Buscar usuários
$stmt = $conn->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Sistema de Gerenciamento de Maquetes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Gerenciar Usuários</h2>
                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $alertTipo; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Novo Usuário</h5>
                        <form method="POST" class="row g-3" id="userForm">
                            <input type="hidden" name="id" id="userId">
                            <div class="col-md-4">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-2">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="admin">Admin</option>
                                    <option value="projetista">Projetista</option>
                                    <option value="montador">Montador</option>
                                    <option value="usuario">Usuário</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha">
                                <small class="form-text text-muted">Deixe em branco para manter a senha atual ao editar</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="limparFormulario()">
                                    <i class="bi bi-plus-circle"></i> Novo
                                </button>
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
                        <h5 class="card-title mb-3">Usuários Cadastrados</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['tipo']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick='editarUsuario(<?php echo json_encode($usuario); ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="excluirUsuario(<?php echo $usuario['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarUsuario(usuario) {
            document.getElementById("userId").value = usuario.id;
            document.getElementById("nome").value = usuario.nome;
            document.getElementById("email").value = usuario.email;
            document.getElementById("tipo").value = usuario.tipo;
            document.getElementById("senha").value = "";

            // Rolar até o formulário
            document.getElementById("userForm").scrollIntoView({
                behavior: 'smooth'
            });
        }

        function limparFormulario() {
            document.getElementById("userId").value = "";
            document.getElementById("nome").value = "";
            document.getElementById("email").value = "";
            document.getElementById("tipo").value = "usuario";
            document.getElementById("senha").value = "";
        }

        function excluirUsuario(id) {
            if (confirm("Tem certeza que deseja excluir este usuário?")) {
                fetch(`delete_usuario.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert("Erro ao excluir usuário: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Erro ao excluir usuário: " + error);
                    });
            }
        }
    </script>
</body>

</html>