<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'wl';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Listar todas as tabelas
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h2>Tabelas encontradas no banco de dados 'wl':</h2>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li><strong>$tabela</strong>";

        // Mostrar estrutura da tabela
        $stmt = $conn->query("DESCRIBE $tabela");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<ul>";
        foreach ($colunas as $coluna) {
            echo "<li>{$coluna['Field']} - {$coluna['Type']}</li>";
        }
        echo "</ul>";

        // Mostrar quantidade de registros
        $stmt = $conn->query("SELECT COUNT(*) FROM $tabela");
        $count = $stmt->fetchColumn();
        echo "<p>Total de registros: $count</p>";
        echo "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "Erro ao acessar banco de dados 'wl': " . $e->getMessage();
}
