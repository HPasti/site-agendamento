<?php
// Arquivo: config.php
// Faz a conexão com o banco de dados MySQL

$host = 'localhost';      // ou 127.0.0.1
$db   = 'agenda_escola'; // coloque aqui o nome do seu banco
$user = 'root';           // nome do usuário (no XAMPP é root)
$pass = '';               // senha (geralmente vazia no XAMPP)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}
?>
