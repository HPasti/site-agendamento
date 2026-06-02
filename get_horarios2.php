<?php
// get_horarios.php
header('Content-Type: application/json; charset=utf-8');
require_once 'auth.php';
$pdo = getPDO();

$recurso = $_GET['recurso'] ?? '';
$data = $_GET['data'] ?? '';
$ilha = $_GET['ilha'] ?? '';

if(!$recurso || !$data){
    echo json_encode([]);
    exit;
}

// validar data e não permitir fim de semana
$timestamp = strtotime($data);
if($timestamp === false){
    echo json_encode([]);
    exit;
}
$dow = (int)date('N', $timestamp);
if($dow >= 6){
    echo json_encode([]);
    exit;
}

// mapa de horários
$HORARIOS = [
    1 => "1ª aula (07:00 - 07:50)",
    2 => "2ª aula (07:50 - 08:40)",
    3 => "3ª aula (08:40 - 09:30)",
    4 => "4ª aula (09:50 - 10:40)",
    5 => "5ª aula (10:40 - 11:30)",
    6 => "6ª aula (11:30 - 12:20)",
    7 => "7ª aula (13:10 - 14:00)",
];

// buscar recurso na tabela para tipo/quantidade_total
$stmt = $pdo->prepare("SELECT id,nome,tipo,quantidade_total FROM recursos WHERE nome = ? LIMIT 1");
$stmt->execute([$recurso]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

// limites fallback para Chromebooks/recursos que não têm quantidade_total
$LIMITES = [
    "Chromebook-1" => 34,
    "Chromebook-2" => 35,
    "Caneta 3D"    => 20,
    "Óculos VR"    => 20,
    "Tablet"       => 20
];

// normalização simples (já tinha no seu código; mantive)
function normalize_str($s){
    $s = mb_strtolower(trim($s));
    $s = str_replace(['á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç'], ['a','a','a','a','e','e','i','o','o','o','u','u','c'], $s);
    return $s;
}
$rnorm = normalize_str($recurso);
$fixed_blocks = [];
if(strpos($rnorm,'auditorio') !== false){
    $fixed_blocks = [1 => [2,3]];
}
if(strpos($rnorm,'lied') !== false){
    $fixed_blocks = [
        1 => [1,2,3,4,5,6,7],
        2 => [1,2,3,4,5,6,7],
        3 => [1,2,3,4,5,6],
        4 => [1,2,3,4,5,7],
        5 => [1,2,3,4,5,6,7],
    ];
}
$fixed_for_day = isset($fixed_blocks[$dow]) ? $fixed_blocks[$dow] : [];

// função auxiliar: tenta encontrar possíveis valores de "dia" no seu DB para o $dow
function possible_day_strings($dow){
    // $dow: 1 (Segunda) ... 5 (Sexta)
    $map = [
        1 => ['Segunda','Segunda-feira','segunda','segunda-feira','2','2ª','Segunda Feira'],
        2 => ['Terca','Terça','Terca-feira','Terça-feira','terça','3','3ª','Terca Feira'],
        3 => ['Quarta','Quarta-feira','quarta','quarta-feira','4','4ª','Quarta Feira'],
        4 => ['Quinta','Quinta-feira','quinta','quinta-feira','5','5ª','Quinta Feira'],
        5 => ['Sexta','Sexta-feira','sexta','sexta-feira','6','6ª','Sexta Feira']
    ];
    return $map[$dow] ?? [];
}

// para cada horário, verificar se está disponível
$available = [];
foreach($HORARIOS as $id => $label){
    // se bloqueado por agendamento fixo
    if(in_array($id, $fixed_for_day)) continue;

    // === NOVO: verificar se existe "ELETIVA" nesse dia/aula em qualquer turma ===
    // se houver, pulamos esse horário (não disponível)
    $possible_days = possible_day_strings($dow);
    if(!empty($possible_days)){
        // montar placeholders dinamicamente
        $placeholders = implode(',', array_fill(0, count($possible_days), '?'));
        // aula pode estar salva como '1', '1ª', '1ª aula', etc. Vamos tentar casar por igualdade e por LIKE
        $sql = "SELECT COUNT(*) FROM horarios 
                WHERE (dia IN ($placeholders))
                  AND (aula = ? OR aula LIKE ?)
                  AND UPPER(disciplina) LIKE ?";
        $params = array_merge($possible_days, [ (string)$id, (string)$id . '%', '%ELETIV%' ]);
        $stmtE = $pdo->prepare($sql);
        $stmtE->execute($params);
        $countE = (int)$stmtE->fetchColumn();
        if($countE > 0){
            // existe ELETIVA neste dia/aula — bloquear
            continue;
        }
    }

    // verificar agendamentos já existentes
    if(strpos($rnorm,'chromebook') !== false){
        // chromebook: precisa informar ilha — se não tiver ilha, não mostra nada
        if(!$ilha) continue;
        $limite = $LIMITES["Chromebook-".$ilha] ?? $LIMITES["Chromebook-1"];

        $stmt2 = $pdo->prepare('SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=? AND ilha=?');
        $stmt2->execute([$recurso, $data, $id, $ilha]);
        $usados = (int)$stmt2->fetchColumn();
        if($usados >= $limite) {
            // indisponível
            continue;
        } else {
            $available[] = ['id'=>$id,'label'=>$label];
            continue;
        }
    }

    // se recurso é equipamento com quantidade_total
    if($res && $res['tipo'] === 'equipamento'){
        $limite = is_null($res['quantidade_total']) ? ($LIMITES[$recurso] ?? 20) : intval($res['quantidade_total']);
        $stmt2 = $pdo->prepare('SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=?');
        $stmt2->execute([$recurso, $data, $id]);
        $usados = (int)$stmt2->fetchColumn();
        if($usados >= $limite){
            continue;
        } else {
            $available[] = ['id'=>$id,'label'=>$label];
            continue;
        }
    }

    // se ambiente (tipo ambiente) ou recurso não cadastrado como equipamento → apenas verificar se existe um agendamento exclusivo
    $stmt3 = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE recurso=? AND data=? AND horario=?');
    $stmt3->execute([$recurso, $data, $id]);
    $count = (int)$stmt3->fetchColumn();
    if($count > 0){
        // já agendado — bloquear
        continue;
    } else {
        $available[] = ['id'=>$id,'label'=>$label];
    }
}

// enviar JSON com horários disponíveis
echo json_encode($available, JSON_UNESCAPED_UNICODE);
exit;
