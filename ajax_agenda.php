<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

$recurso = $_POST['recurso'] ?? '';
$dia = $_POST['data'] ?? '';
$horario = intval($_POST['horario'] ?? 0);
$quantidade = intval($_POST['quantidade'] ?? 1);

// CORREÇÃO #7: definir Content-Type antes de qualquer saída JSON
header('Content-Type: application/json; charset=utf-8');

if(!$recurso || !$dia || !$horario){
    echo json_encode(['ok'=>false,'error'=>'Dados incompletos']);
    exit;
}

// insere
$stmt=$pdo->prepare("INSERT INTO agendamentos (user_id,recurso,data,horario,quantidade) VALUES (?,?,?,?,?)");
$stmt->execute([$_SESSION['user_id'],$recurso,$dia,$horario,$quantidade]);

echo json_encode(['ok'=>true]);
