<?php
session_start();
require_once 'auth.php';
$pdo=getPDO();
requireLogin();

$dia=$_POST['dia']??'';
$horario=$_POST['horario']??'';
$recurso=$_POST['recurso']??'';

// CORREÇÃO #7: definir Content-Type antes de qualquer saída JSON
header('Content-Type: application/json; charset=utf-8');

if(!$dia || !$horario || !$recurso){
  echo json_encode(['success'=>false,'error'=>'Dados incompletos']);
  exit;
}

$stmt=$pdo->prepare("INSERT INTO agendamentos (user_id,recurso,data,horario) VALUES (?,?,?,?)");
$stmt->execute([$_SESSION['user_id'],$recurso,$dia,$horario]);
echo json_encode(['success'=>true]);
