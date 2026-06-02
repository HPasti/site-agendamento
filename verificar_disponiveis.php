<?php
// CORREÇÃO #6: adicionar session_start() e requireLogin()
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

// CORREÇÃO #15: header Content-Type definido no início, antes de qualquer echo
header('Content-Type: application/json; charset=utf-8');

$recurso = $_GET['recurso'] ?? '';
$data    = $_GET['data']    ?? '';
$horario = $_GET['horario'] ?? '';
$ilha    = $_GET['ilha']    ?? null;

// Limites fixos dos Chromebooks
$LIMITES = [1 => 34, 2 => 35];

// Tipo e total
$stmt = $pdo->prepare("SELECT tipo, quantidade_total FROM recursos WHERE nome = ?");
$stmt->execute([$recurso]);
$recursoInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recursoInfo) {
    echo json_encode(['disponiveis' => 0]);
    exit;
}

// Chromebook → verifica por ilha
if (strtolower($recurso) === 'chromebook' && $ilha) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=? AND ilha=?");
    $stmt->execute([$recurso, $data, $horario, $ilha]);
    $usados     = (int)$stmt->fetchColumn();
    $total      = $LIMITES[$ilha] ?? 0;
    $disponiveis = max(0, $total - $usados);
}
// Equipamentos → usa quantidade_total
elseif ($recursoInfo['tipo'] === 'equipamento') {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=?");
    $stmt->execute([$recurso, $data, $horario]);
    $usados      = (int)$stmt->fetchColumn();
    $total       = (int)$recursoInfo['quantidade_total'];
    $disponiveis = max(0, $total - $usados);
}
// Ambientes → sempre disponíveis (1 por horário)
else {
    $disponiveis = 1;
}

echo json_encode(['disponiveis' => $disponiveis]);
