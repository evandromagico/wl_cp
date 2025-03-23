<?php
require_once '../config/database.php';

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

try {
    // Desabilitar verificação de chave estrangeira
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Primeiro, fazer backup dos dados existentes
    $stmt = $conn->query("SELECT * FROM projetos");
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fazer backup dos dados das tabelas relacionadas
    $stmt = $conn->query("SELECT * FROM status_desenho");
    $status_desenho = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("SELECT * FROM status_corte");
    $status_corte = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("SELECT * FROM status_montagem");
    $status_montagem = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Remover as tabelas
    $conn->exec("DROP TABLE IF EXISTS status_desenho");
    $conn->exec("DROP TABLE IF EXISTS status_corte");
    $conn->exec("DROP TABLE IF EXISTS status_montagem");
    $conn->exec("DROP TABLE IF EXISTS equipe_projeto");
    $conn->exec("DROP TABLE IF EXISTS projetos");

    // Recriar a tabela projetos
    $conn->exec("CREATE TABLE projetos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        data_entrega DATE NOT NULL,
        status VARCHAR(50) DEFAULT 'Em dia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Recriar as tabelas relacionadas
    $conn->exec("CREATE TABLE status_desenho (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        torre_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        internos_torre_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        internos_embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Enviado', 'Concluído') DEFAULT 'Não Iniciado',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE status_corte (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        estrutura_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        cobertura_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        acabamentos_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        internos_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        embasamento_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        lazer_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        mobiliario_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        arborismo_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE status_montagem (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        estrutura INT NOT NULL DEFAULT 0,
        acabamentos INT NOT NULL DEFAULT 0,
        base INT NOT NULL DEFAULT 0,
        detalhes INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (projeto_id),
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE equipe_projeto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        usuario_id INT NOT NULL,
        tipo_trabalho VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // Restaurar os dados dos projetos
    if (!empty($projetos)) {
        $stmt = $conn->prepare("INSERT INTO projetos (id, nome, data_entrega, status, created_at) VALUES (?, ?, ?, ?, ?)");
        foreach ($projetos as $projeto) {
            $stmt->execute([
                $projeto['id'],
                $projeto['nome'],
                $projeto['data_entrega'],
                calcularStatusProjeto($projeto['data_entrega']),
                $projeto['created_at']
            ]);
        }
    }

    // Restaurar os dados das tabelas relacionadas
    if (!empty($status_desenho)) {
        $stmt = $conn->prepare("INSERT INTO status_desenho (projeto_id, torre_status, embasamento_status, internos_torre_status, internos_embasamento_status) VALUES (?, ?, ?, ?, ?)");
        foreach ($status_desenho as $status) {
            $stmt->execute([
                $status['projeto_id'],
                $status['torre_status'],
                $status['embasamento_status'],
                $status['internos_torre_status'],
                $status['internos_embasamento_status']
            ]);
        }
    }

    if (!empty($status_corte)) {
        $stmt = $conn->prepare("INSERT INTO status_corte (projeto_id, estrutura_status, cobertura_status, acabamentos_status, internos_status, embasamento_status, lazer_status, mobiliario_status, arborismo_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($status_corte as $status) {
            $stmt->execute([
                $status['projeto_id'],
                $status['estrutura_status'],
                $status['cobertura_status'],
                $status['acabamentos_status'],
                $status['internos_status'],
                $status['embasamento_status'],
                $status['lazer_status'],
                $status['mobiliario_status'],
                $status['arborismo_status']
            ]);
        }
    }

    if (!empty($status_montagem)) {
        $stmt = $conn->prepare("INSERT INTO status_montagem (projeto_id, estrutura, acabamentos, base, detalhes) VALUES (?, ?, ?, ?, ?)");
        foreach ($status_montagem as $status) {
            $stmt->execute([
                $status['projeto_id'],
                $status['estrutura'],
                $status['acabamentos'],
                $status['base'],
                $status['detalhes']
            ]);
        }
    }

    // Recriar o trigger
    $conn->exec("DROP TRIGGER IF EXISTS after_projeto_insert");
    $conn->exec("
        CREATE TRIGGER after_projeto_insert 
        AFTER INSERT ON projetos
        FOR EACH ROW
        BEGIN
            INSERT INTO status_desenho (projeto_id) VALUES (NEW.id);
            INSERT INTO status_corte (projeto_id) VALUES (NEW.id);
            INSERT INTO status_montagem (projeto_id) VALUES (NEW.id);
        END
    ");

    // Reabilitar verificação de chave estrangeira
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Tabelas recriadas com sucesso!<br>";
    echo "Você pode voltar para o <a href='../projetos.php'>Gerenciar Projetos</a>";
} catch (PDOException $e) {
    // Em caso de erro, tentar reabilitar a verificação de chave estrangeira
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Erro: " . $e->getMessage();
}
