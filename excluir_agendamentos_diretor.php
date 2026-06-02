<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['agendamento_id'])) {
    $agendamento_id = (int)$_POST['agendamento_id'];

    // Verificar se o usuário é diretor (de forma segura)
    if (($_SESSION['user_role'] ?? '') !== 'diretor') {
        die("Acesso negado!");
    }

    // Buscar o agendamento
    $stmt = $pdo->prepare("SELECT recurso, data, horario FROM agendamentos WHERE id = ?");
    $stmt->execute([$agendamento_id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($agendamento) {
        // Aqui você pode atualizar disponibilidade do recurso, se precisar

        // Apagar o agendamento
        $stmt_del = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
        $stmt_del->execute([$agendamento_id]);
    }

    // Redirecionar após exclusão
    header("Location: pagina_diretor.php");
    exit;
} else {
    die("Requisição inválida.");
}
?>
