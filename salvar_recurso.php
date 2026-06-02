<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $quantidade = $_POST['quantidade_total'] ?? null;
    $materias = $_POST['materias_restritas'] ?? [];

    if (!$nome || !$tipo) {
        die("Erro: campos obrigatórios não preenchidos.");
    }

    // Converter array de matérias para JSON
    $materiasJson = !empty($materias) ? json_encode($materias, JSON_UNESCAPED_UNICODE) : null;

    // Usuário criador (opcional)
    $criado_por = $_SESSION['user_id'] ?? null;

    // Inserir no banco
    $stmt = $pdo->prepare("
        INSERT INTO recursos (nome, tipo, quantidade_total, materias_restritas, criado_por, criado_em)
        VALUES (:nome, :tipo, :quantidade_total, :materias_restritas, :criado_por, NOW())
    ");
    $stmt->execute([
        ':nome' => $nome,
        ':tipo' => $tipo,
        ':quantidade_total' => ($tipo === 'equipamento' ? $quantidade : null),
        ':materias_restritas' => $materiasJson,
        ':criado_por' => $criado_por
    ]);

    header("Location: recursos_gen.php");
    exit;
}
?>
