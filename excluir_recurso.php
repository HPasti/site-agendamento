<?php
session_start();
require_once 'auth.php';
requireDirector();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Verifica se o recurso existe
        $stmt = $pdo->prepare("SELECT * FROM recursos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $recurso = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recurso) {
            // Excluir agendamentos vinculados a este recurso pelo nome
            $pdo->prepare("DELETE FROM agendamentos WHERE recurso = :nome")->execute([':nome' => $recurso['nome']]);

            // Excluir o recurso em si
            $delete = $pdo->prepare("DELETE FROM recursos WHERE id = :id");
            $delete->execute([':id' => $id]);
        }
    }
}

// Redireciona de volta à página de gerenciamento
header("Location: recursos_gen.php");
exit;
?>
