<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'maquetaria_db';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar tabelas
    $tables = ['projetos', 'status_desenho', 'status_corte', 'status_montagem'];

    echo "<h2>Status das Tabelas:</h2>";
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW CREATE TABLE $table");
        if ($stmt) {
            $result = $stmt->fetch();
            echo "<strong>Tabela $table:</strong> Existe<br>";
            echo "Estrutura:<br>";
            echo "<pre>" . htmlspecialchars($result[1]) . "</pre><br>";
        } else {
            echo "<strong>Tabela $table:</strong> Não existe<br>";
        }
    }

    // Verificar trigger
    $stmt = $conn->query("SHOW TRIGGERS WHERE `Trigger` = 'after_projeto_insert'");
    $trigger = $stmt->fetch();

    echo "<h2>Status do Trigger:</h2>";
    if ($trigger) {
        echo "<strong>Trigger after_projeto_insert:</strong> Existe<br>";
        echo "Estrutura:<br>";
        echo "<pre>" . htmlspecialchars($trigger['Statement']) . "</pre>";
    } else {
        echo "<strong>Trigger after_projeto_insert:</strong> Não existe<br>";
    }
} catch (PDOException $e) {
    echo "Erro ao verificar tabelas: " . $e->getMessage();
}
