<?php
// CORREÇÃO #6: adicionar session_start() e requireLogin()
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$ilha    = $_GET['ilha']    ?? null;
$data    = $_GET['data']    ?? null;
$horario = $_GET['horario'] ?? null;

if (!$ilha || !$data || !$horario) {
    echo json_encode(['ocupados' => 0]);
    exit;
}

$sql = "SELECT SUM(quantidade) as ocupados
        FROM agendamentos
        WHERE recurso = 'Chromebook'
          AND ilha    = ?
          AND data    = ?
          AND horario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ilha, $data, $horario]);
$row = $stmt->fetch();

echo json_encode(['ocupados' => $row['ocupados'] ?? 0]);
