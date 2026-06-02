<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

header('Content-Type: application/json; charset=utf-8');

// Horários padrão (ID => TEXTO)
$HORARIOS = [
    1 => "1ª aula (07:00 - 07:50)",
    2 => "2ª aula (07:50 - 08:40)",
    3 => "3ª aula (08:40 - 09:30)",
    4 => "4ª aula (09:50 - 10:40)",
    5 => "5ª aula (10:40 - 11:30)",
    6 => "6ª aula (11:30 - 12:20)",
    7 => "7ª aula (13:10 - 14:00)"
];

$recurso = trim($_GET['recurso'] ?? '');
$data    = trim($_GET['data']    ?? '');

if (!$recurso || !$data) {
    echo json_encode(['error' => 'Parâmetros insuficientes.']);
    exit;
}

try {
    // CORREÇÃO #1: buscar os IDs inteiros ocupados (não os labels de texto)
    $stmt = $pdo->prepare("
        SELECT horario
        FROM agendamentos
        WHERE recurso = ? AND data = ?
    ");
    $stmt->execute([$recurso, $data]);
    $ocupadosIDs = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    // Montar resposta com os horários ainda não ocupados
    $response = [
        'restrito'             => false,
        'horarios_disponiveis' => []
    ];

    foreach ($HORARIOS as $id => $label) {
        if (!in_array($id, $ocupadosIDs, true)) {
            $response['horarios_disponiveis'][] = [
                'id'    => $id,
                'label' => $label
            ];
        }
    }

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno. Tente novamente.']);
    exit;
}
