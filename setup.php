<?php
require_once 'auth.php';

try {
    $pdo = getPDO();

    // Mostrar tabelas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h2>Tabelas encontradas:</h2><pre>";
    print_r($tables);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h2>Erro de conexão:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
