<?php
if (!isset($_SESSION)) {
    session_start();
}

$usuario_tipo = $_SESSION['usuario_tipo'] ?? '';
$usuario_nome = $_SESSION['usuario_nome'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Sistema de Maquetes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <?php if ($usuario_tipo == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="projetos.php">Projetos</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-text me-3">
                Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>
            </div>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>