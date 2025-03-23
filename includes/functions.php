<?php
// Função para calcular o status do projeto baseado na data de entrega
function calcularStatusProjeto($dataEntrega)
{
    $hoje = new DateTime();
    $dataEntrega = new DateTime($dataEntrega);
    $diff = $hoje->diff($dataEntrega);

    if ($dataEntrega < $hoje) {
        return "Atrasado " . $diff->days . " dias";
    } else if ($diff->days <= 3) {
        return "Atrasando";
    } else {
        return "Em dia";
    }
}

// Função para verificar se o usuário está logado
function verificarLogin()
{
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Função para verificar permissões do usuário
function verificarPermissao($tipoPermissao)
{
    if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != $tipoPermissao) {
        header('Location: index.php?erro=sem_permissao');
        exit();
    }
}

// Função para formatar data
function formatarData($data)
{
    return date('d/m/Y', strtotime($data));
}

// Função para calcular porcentagem de conclusão
function calcularPorcentagem($concluidos, $total)
{
    if ($total == 0) return 0;
    return round(($concluidos / $total) * 100);
}

// Função para gerar cor baseada no status
function getStatusColor($status)
{
    if (strpos($status, 'Atrasado') !== false) {
        return 'danger';
    } else if ($status == 'Atrasando') {
        return 'warning';
    } else {
        return 'success';
    }
}

// Função para sanitizar input
function sanitizar($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

// Função para gerar mensagem de alerta
function gerarAlerta($tipo, $mensagem)
{
    return "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                {$mensagem}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

// Função para validar data
function validarData($data)
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

// Função para converter status em porcentagem
function statusParaPorcentagem($status)
{
    switch ($status) {
        case 'Concluído':
            return 100;
        case 'Em Execução':
            return 50;
        case 'Enviado':
            return 75;
        case 'Em Revisão':
            return 60;
        case 'Não Iniciado':
        default:
            return 0;
    }
}

function calcularProgressoTotal($status_desenho, $status_corte, $status_montagem)
{
    // Cada etapa tem um peso igual (33.33%)
    $progresso = ($status_desenho + $status_corte + $status_montagem) / 3;
    return round($progresso);
}
