<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    // Verifica se pertence ao usuário logado
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    header("Location: ver_agendamentos.php");
    exit;
} else {
    header("Location: ver_agendamentos.php");
    exit;
}
