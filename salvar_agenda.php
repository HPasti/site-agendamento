<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = $_SESSION['user_id'];
    $data      = $_POST['data'] ?? '';
    $horario   = $_POST['horario'] ?? '';
    $recurso   = trim($_POST['recurso'] ?? '');

    if(!$data || !$horario || !$recurso){
        $_SESSION['erro']="Preencha todos os campos.";
        header("Location: calendario.php?view=day&dia=".urlencode($data));
        exit;
    }

    if($recurso==='Chromebook'){
        $ilha1 = intval($_POST['ilha1_quant'] ?? 0);
        $ilha2 = intval($_POST['ilha2_quant'] ?? 0);

        // Verifica limite de cada ilha
        $stmt = $pdo->prepare("SELECT SUM(quantidade) FROM agendamentos WHERE data=? AND horario=? AND recurso=? AND ilha=?");
        $stmt->execute([$data,$horario,$recurso,1]);
        $total1 = intval($stmt->fetchColumn());
        $stmt->execute([$data,$horario,$recurso,2]);
        $total2 = intval($stmt->fetchColumn());

        if($ilha1 + $total1 > 34 || $ilha2 + $total2 > 35){
            $_SESSION['erro']="Quantidade excede limite disponível das ilhas.";
            header("Location: calendario.php?view=day&dia=".urlencode($data));
            exit;
        }

        if($ilha1>0){
            $stmt = $pdo->prepare("INSERT INTO agendamentos (user_id,data,horario,recurso,quantidade,ilha) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$user_id,$data,$horario,$recurso,$ilha1,1]);
        }
        if($ilha2>0){
            $stmt = $pdo->prepare("INSERT INTO agendamentos (user_id,data,horario,recurso,quantidade,ilha) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$user_id,$data,$horario,$recurso,$ilha2,2]);
        }

        $_SESSION['sucesso']="Agendamento de Chromebook realizado!";
    } else {
        $quantidade = intval($_POST['quantidade'] ?? 1);

        // CORREÇÃO #14: usar transação para evitar race condition
        try {
            $pdo->beginTransaction();
            // SELECT FOR UPDATE garante que nenhuma outra requisição simultânea passe antes
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data=? AND horario=? AND recurso=? FOR UPDATE");
            $stmt->execute([$data,$horario,$recurso]);
            $existe = $stmt->fetchColumn();

            if($existe > 0){
                $pdo->rollBack();
                $_SESSION['erro'] = "Esse recurso já está agendado nesse horário.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO agendamentos (user_id,data,horario,recurso,quantidade) VALUES (?,?,?,?,?)");
                $stmt->execute([$user_id,$data,$horario,$recurso,$quantidade]);
                $pdo->commit();
                $_SESSION['sucesso'] = "Agendamento realizado!";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['erro'] = "Erro ao realizar agendamento. Tente novamente.";
            error_log('[salvar_agenda] ' . $e->getMessage());
        }
    }

    header("Location: calendario.php?view=day&dia=".urlencode($data));
    exit;
}
