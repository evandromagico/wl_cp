<?php
// Configurar timezone para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Função para calcular o status do projeto baseado na data de entrega
function calcularStatusProjeto($data_entrega)
{
    // Converter as datas para timestamp (meia-noite)
    $data_atual = strtotime(date('Y-m-d'));
    $data_entrega = strtotime(date('Y-m-d', strtotime($data_entrega)));

    // Se a data atual é depois da data de entrega
    if ($data_atual > $data_entrega) {
        $dias = floor(($data_atual - $data_entrega) / (60 * 60 * 24));
        return "Atrasado {$dias} dias";
    }

    // Se é o mesmo dia
    if ($data_atual == $data_entrega) {
        return "Atrasando";
    }

    // Calcular dias restantes
    $dias_restantes = floor(($data_entrega - $data_atual) / (60 * 60 * 24));

    // Se faltam 3 dias ou menos
    if ($dias_restantes <= 3) {
        return "Atrasando";
    }

    // Se faltam mais de 3 dias
    return "Em dia";
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

// Função para gerar cor e texto baseados no status
function getStatusColor($data_entrega, $progresso_total = 0)
{
    // Se o progresso total for 100%, retorna azul e "Concluído"
    if ($progresso_total >= 100) {
        return [
            'color' => 'primary', // Azul
            'text' => 'Concluído'
        ];
    }

    // Converter as datas para timestamp (meia-noite)
    $data_atual = strtotime(date('Y-m-d'));
    $data_entrega = strtotime(date('Y-m-d', strtotime($data_entrega)));

    // Se a data atual é depois da data de entrega
    if ($data_atual > $data_entrega) {
        $dias = floor(($data_atual - $data_entrega) / (60 * 60 * 24));
        return [
            'color' => 'danger', // Vermelho para atrasado
            'text' => "Atrasado {$dias} dias"
        ];
    }

    // Calcular dias restantes
    $dias_restantes = floor(($data_entrega - $data_atual) / (60 * 60 * 24));

    // Se faltam mais de 3 dias
    if ($dias_restantes > 3) {
        return [
            'color' => 'success', // Verde
            'text' => 'Em dia'
        ];
    }

    // Se faltam 3 dias ou menos (incluindo mesmo dia)
    if ($progresso_total < 90) {
        return [
            'color' => 'orange', // Laranja
            'text' => 'Atrasando crítico'
        ];
    } else {
        return [
            'color' => 'warning', // Amarelo
            'text' => 'Atrasando'
        ];
    }
}

// Função para calcular dias atrasados ou restantes
function calcularDiasAtrasados($data_entrega)
{
    // Converter as datas para timestamp (meia-noite)
    $data_atual = strtotime(date('Y-m-d'));
    $data_entrega = strtotime(date('Y-m-d', strtotime($data_entrega)));

    // Calcular a diferença em dias
    return floor(($data_atual - $data_entrega) / (60 * 60 * 24));
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

// Função para calcular progresso do desenho
function calcularProgressoDesenho($projeto)
{
    $status_map = [
        'Não Enviado' => 0,
        'Em Revisão' => 60,
        'Enviado' => 100
    ];

    $campos = ['torre_status', 'embasamento_status', 'internos_torre_status', 'internos_embasamento_status'];

    $total = 0;
    $count = 0;

    foreach ($campos as $campo) {
        if (isset($projeto[$campo])) {
            $status = $projeto[$campo] ?? 'Não Enviado';
            // Se o status não existir no mapa, usa 'Não Enviado'
            $total += $status_map[$status] ?? $status_map['Não Enviado'];
            $count++;
        }
    }

    return $count > 0 ? round($total / $count) : 0;
}

// Função para calcular progresso do corte
function calcularProgressoCorte($projeto)
{
    $status_map = [
        'Não Iniciado' => 0,
        'Em Andamento' => 50,
        'Concluído' => 100
    ];

    $campos = [
        'estrutura_status',
        'cobertura_status',
        'acabamentos_status',
        'internos_status',
        'embasamento_status',
        'lazer_status',
        'mobiliario_status',
        'arborismo_status'
    ];

    $total = 0;
    $count = 0;

    foreach ($campos as $campo) {
        if (isset($projeto[$campo])) {
            $status = $projeto[$campo] ?? 'Não Iniciado';
            if (isset($status_map[$status])) {
                $total += $status_map[$status];
                $count++;
            }
        }
    }

    return $count > 0 ? round($total / $count) : 0;
}

// Função para calcular progresso da montagem
function calcularProgressoMontagem($projeto)
{
    $status_map = [
        0 => 0,      // Não Iniciado
        1 => 50,     // Em Execução
        2 => 100     // Concluído
    ];

    $campos = ['estrutura', 'cobertura', 'acabamentos', 'internos', 'lazer', 'mobiliario', 'arborismo'];

    $total = 0;
    $count = 0;

    foreach ($campos as $campo) {
        if (isset($projeto[$campo])) {
            $total += $status_map[$projeto[$campo] ?? 0];
            $count++;
        }
    }

    return $count > 0 ? round($total / $count) : 0;
}
