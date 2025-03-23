<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Conectar ao banco wl
    $wl_conn = new PDO("mysql:host=$host;dbname=wl", $user, $pass);
    $wl_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Conectar ao banco maquetaria_db
    $maq_conn = new PDO("mysql:host=$host;dbname=maquetaria_db", $user, $pass);
    $maq_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Migrar usuários ativos
    echo "<h3>Migrando usuários...</h3>";
    $stmt = $wl_conn->query("SELECT * FROM usuarios WHERE status = 'ativo'");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        try {
            $stmt = $maq_conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $tipo = ($usuario['nivel'] == '1') ? 'admin' : 'usuario';
            $stmt->execute([$usuario['nome'], $usuario['email'], $usuario['senha'], $tipo]);
            echo "Usuário {$usuario['nome']} migrado com sucesso<br>";
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { // Ignora erros de duplicidade
                echo "Erro ao migrar usuário {$usuario['nome']}: " . $e->getMessage() . "<br>";
            }
        }
    }

    // 2. Migrar projetos ativos
    echo "<h3>Migrando projetos...</h3>";
    $stmt = $wl_conn->query("
        SELECT d.*, e.nome as empresa_nome, emp.nome as empreendimento_nome 
        FROM desenhos d 
        LEFT JOIN empresa e ON d.empresa = e.id 
        LEFT JOIN empreendimentos emp ON d.empreendimento = emp.id 
        WHERE d.status != 'Excluido'
    ");
    $desenhos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($desenhos as $desenho) {
        try {
            // Inserir projeto
            $stmt = $maq_conn->prepare("
                INSERT INTO projetos (nome, data_entrega, status) 
                VALUES (?, CURDATE() + INTERVAL 30 DAY, ?)
            ");
            $nome_projeto = $desenho['empreendimento_nome'] . ' - ' . $desenho['nome'];
            $stmt->execute([$nome_projeto, $desenho['status']]);
            $projeto_id = $maq_conn->lastInsertId();

            // Inserir status inicial do desenho
            $stmt = $maq_conn->prepare("
                INSERT INTO status_desenho (
                    projeto_id, 
                    torre_status, 
                    embasamento_status
                ) VALUES (?, ?, ?)
            ");

            // Converter status do wl para o novo formato
            $status_map = [
                'Finalizado' => 'Concluído',
                'Em Execução' => 'Em Andamento',
                'Aguardando' => 'Não Iniciado',
                'Pendente' => 'Não Iniciado'
            ];

            $status_desenho = $status_map[$desenho['status']] ?? 'Não Iniciado';
            $stmt->execute([$projeto_id, $status_desenho, $status_desenho]);

            // Buscar status de corte relacionado
            $stmt = $wl_conn->prepare("
                SELECT * FROM corte 
                WHERE id_desenho = ? 
                ORDER BY data_add DESC 
                LIMIT 1
            ");
            $stmt->execute([$desenho['id']]);
            $corte = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($corte) {
                $status_corte = $status_map[$corte['status']] ?? 'Não Iniciado';
                $stmt = $maq_conn->prepare("
                    INSERT INTO status_corte (
                        projeto_id,
                        estrutura_status,
                        acabamentos_status
                    ) VALUES (?, ?, ?)
                ");
                $stmt->execute([$projeto_id, $status_corte, $status_corte]);
            }

            // Inserir status inicial da montagem
            $stmt = $maq_conn->prepare("
                INSERT INTO status_montagem (
                    projeto_id,
                    estrutura,
                    acabamentos,
                    base,
                    detalhes
                ) VALUES (?, 0, 0, 0, 0)
            ");
            $stmt->execute([$projeto_id]);

            echo "Projeto {$nome_projeto} migrado com sucesso<br>";
        } catch (PDOException $e) {
            echo "Erro ao migrar projeto {$desenho['nome']}: " . $e->getMessage() . "<br>";
        }
    }

    echo "<h3>Migração concluída!</h3>";
    echo "<p>Você pode voltar para o <a href='../dashboard.php'>Dashboard</a></p>";
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
