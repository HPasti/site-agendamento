<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

$horarios_post = $_POST['horarios'] ?? [];

$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM horarios");

    $stmt = $pdo->prepare("INSERT INTO horarios (dia, turma, aula, disciplina) VALUES (:dia, :turma, :aula, :disciplina)");

    foreach($horarios_post as $dia => $turmas) {
        foreach($turmas as $turma => $aulas) {
            foreach($aulas as $aula => $disciplina) {
                if(!empty($disciplina)) {
                    $stmt->execute([
                        ':dia' => $dia,
                        ':turma' => $turma,
                        ':aula' => $aula,
                        ':disciplina' => $disciplina
                    ]);
                }
            }
        }
    }

    $pdo->commit();
    // Redirecionar com mensagem de sucesso via sessão
    $_SESSION['sucesso'] = 'Horários salvos com sucesso!';
    header('Location: editar_horarios.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    // CORREÇÃO #10: logar internamente, nunca expor getMessage() ao usuário
    error_log('[salvar_horarios] ' . $e->getMessage());
    $_SESSION['erro'] = 'Erro ao salvar os horários. Tente novamente.';
    header('Location: editar_horarios.php');
    exit;
}
