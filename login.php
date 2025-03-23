<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$mensagem = '';
$alertTipo = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizar($_POST['email']);
    $senha = $_POST['senha'];

    try {
        $stmt = $conn->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header('Location: dashboard.php');
            exit();
        } else {
            $mensagem = "Email ou senha incorretos";
            $alertTipo = "danger";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao processar login: " . $e->getMessage();
        $alertTipo = "danger";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gerenciamento de Maquetes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="login-form">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Login</h2>
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $alertTipo; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>