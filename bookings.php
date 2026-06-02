<?php
// agendamento_diretor.php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireLogin();

// --- Inserir recursos padrão se não existirem ---
$recursos_padrao = [
    ['Quadra', 'ambiente', null],
    ['Auditório', 'ambiente', null],
    ['LIED', 'ambiente', null],
    ['Chromebook', 'equipamento', 69], // 34 + 35
    ['Laboratório de Ciências', 'ambiente', null],
    ['Caneta 3D', 'equipamento', 20],
    ['Óculos VR', 'equipamento', 20],
    ['Tablet', 'equipamento', 20],
];

foreach ($recursos_padrao as $r) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recursos WHERE nome = ?");
    $stmt->execute([$r[0]]);
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO recursos (nome, tipo, quantidade_total) VALUES (?, ?, ?)");
        $insert->execute([$r[0], $r[1], $r[2]]);
    }
}

// mapa de horários (id => label)
$HORARIOS = [
    1 => "1ª aula (07:00 - 07:50)",
    2 => "2ª aula (07:50 - 08:40)",
    3 => "3ª aula (08:40 - 09:30)",
    4 => "4ª aula (09:50 - 10:40)",
    5 => "5ª aula (10:40 - 11:30)",
    6 => "6ª aula (11:30 - 12:20)",
    7 => "7ª aula (13:10 - 14:00)",
];

// buscar recursos do banco
$stmt = $pdo->query("SELECT id, nome, tipo, quantidade_total FROM recursos ORDER BY nome ASC");
$recursos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// limites fallback (mantive para compatibilidade com Chromebooks caso não haja na tabela)
$LIMITES = [
    "Chromebook-1" => 34,
    "Chromebook-2" => 35,
    "Caneta 3D"    => 20,
    "Óculos VR"    => 20,
    "Tablet"       => 20
];

function normalize_str($s){
    $s = mb_strtolower(trim($s));
    $s = str_replace(['á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü','ç'], ['a','a','a','a','e','e','i','o','o','o','u','u','c'], $s);
    return $s;
}

$message = '';
$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $recurso = $_POST['recurso'] ?? '';
    $data = $_POST['data'] ?? '';
    $horario = isset($_POST['horario']) ? intval($_POST['horario']) : 0;
    $ilha = $_POST['ilha'] ?? '';
    $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;

    if(!$recurso) $errors[] = 'Selecione um recurso.';
    if(!$data) $errors[] = 'Selecione uma data.';
    if($horario < 1 || $horario > 7) $errors[] = 'Selecione um horário válido.';

    // validar data (não permitir fim de semana)
    $timestamp = strtotime($data);
    if($timestamp === false) $errors[] = 'Data inválida.';
    else {
        $dow = (int)date('N', $timestamp);
        if($dow >= 6) $errors[] = 'Não é permitido agendar nos finais de semana.';
    }

    // descobrir limite do recurso (se existir na tabela)
    $limite_recurso = null;
    foreach($recursos_db as $r){
        if($r['nome'] === $recurso){
            $limite_recurso = is_null($r['quantidade_total']) ? null : intval($r['quantidade_total']);
            break;
        }
    }

    // regras específicas
    $rnorm = normalize_str($recurso);

    if(strpos($rnorm,'chromebook') !== false){
        // chromebook: precisa selecionar ilha e quantidade
        if(!$ilha) $errors[] = "Selecione uma ilha.";
        if($quantidade < 1) $errors[] = "Informe uma quantidade válida.";

        $limite = null;
        // tenta ler limite da tabela (usar LIMITES por ilha se não definido)
        if($limite_recurso) {
            // se na tabela houver quantidade_total, assumimos que é total geral; para ilhas mantemos valores padrão
            $limite = $LIMITES["Chromebook-".$ilha] ?? $LIMITES["Chromebook-1"];
        } else {
            $limite = $LIMITES["Chromebook-".$ilha] ?? $LIMITES["Chromebook-1"];
        }

        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=? AND ilha=?');
        $stmt->execute([$recurso,$data,$horario,$ilha]);
        $usados = (int)$stmt->fetchColumn();
        if($usados + $quantidade > $limite){
            $errors[] = "Não há Chromebooks suficientes na Ilha $ilha. Disponíveis: ".max(0, $limite - $usados);
        }
    } else {
        // outros equipamentos que possuem quantidade
        // checar se é equipamento por nome/tipo (quando quantidade_total estiver preenchido na tabela)
        $is_equipamento = false;
        $tipo_do_recurso = null;
        foreach($recursos_db as $r){
            if($r['nome'] === $recurso){
                $tipo_do_recurso = $r['tipo'];
                if($r['tipo'] === 'equipamento') $is_equipamento = true;
                break;
            }
        }

        if($is_equipamento){
            if($quantidade < 1) $errors[] = "Informe uma quantidade válida.";
            $limite = $limite_recurso ?? ($LIMITES[$recurso] ?? 20);

            $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantidade),0) FROM agendamentos WHERE recurso=? AND data=? AND horario=?');
            $stmt->execute([$recurso,$data,$horario]);
            $usados = (int)$stmt->fetchColumn();
            if($usados + $quantidade > $limite){
                $errors[] = "Não há $recurso suficientes. Disponíveis: ".max(0, $limite - $usados);
            }
        } else {
            // se for ambiente, quantidade não é obrigatória — definimos quantidade = NULL
            $quantidade = null;
            $ilha = null;
        }
    }

    // bloqueios fixos com base no nome
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
    if(isset($timestamp)){
        $dow = (int)date('N', $timestamp);
        $fixed_for_day = isset($fixed_blocks[$dow]) ? $fixed_blocks[$dow] : [];
        if(in_array($horario, $fixed_for_day)){
            $errors[] = "Esse horário está bloqueado por agendamento fixo.";
        }
    }

    if(empty($errors)){
        $stmt = $pdo->prepare('INSERT INTO agendamentos (user_id,recurso,data,horario,ilha,quantidade) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$_SESSION['user_id'],$recurso,$data,$horario,$ilha,$quantidade]);
        $message = 'Agendamento realizado com sucesso.';
    } else {
        $message = implode('<br>', $errors);
    }
}

// listar agendamentos do usuário (opcional exibição)
$bookings = $pdo->prepare('SELECT a.*, u.nome FROM agendamentos a JOIN usuarios u ON a.user_id=u.id WHERE a.user_id=? ORDER BY data DESC');
$bookings->execute([$_SESSION['user_id']]);
$my = $bookings->fetchAll(PDO::FETCH_ASSOC);

function label_for_horario($id, $HORARIOS){
    return $HORARIOS[$id] ?? "Aula $id";
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Agendamentos - Diretor</title>
<link rel="stylesheet" href="assets/style.css">
<style>
:root{
  --blue:#2b6fb3;
  --blue-dark:#153a5b;
  --white:#ffffff;
  --red:#b00020;
  --radius:12px;
}

body {
  margin: 0;
  padding: 0;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 8%;
  background: var(--white);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}
.logo{
  position:relative;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:12px;
}
.logo img{height:120px;transition:transform .2s;}
.logo:hover img{transform:scale(1.05);}
.logo::after{
  content:"Abrir barra lateral";
  position:absolute;
  bottom:-10px;
  left:50%;
  transform:translate(-50%,100%);
  background:var(--blue-dark);
  color:var(--white);
  font-size:13px;
  padding:6px 10px;
  border-radius:8px;
  opacity:0;
  pointer-events:none;
  transition:opacity .2s,transform .2s;
  white-space:nowrap;
}
.logo:hover::after{
  opacity:1;
  transform:translate(-50%,110%);
}

/* Sidebar */
.sidebar{
  position:fixed;
  top:0;
  left:-260px;
  width:260px;
  height:100%;
  background:var(--blue-dark);
  color:var(--white);
  transition:left .3s ease;
  padding-top:80px;
  z-index:1000;
}
.sidebar.active{left:0;}
.sidebar ul{list-style:none;padding:0;}
.sidebar ul li{padding:15px 24px;}
.sidebar ul li a {
  color: var(--white);
  text-decoration: none;
  display: block;
  font-weight: normal; /* ou 400 */
  transition: background .2s;
}
.sidebar ul li a:hover{
  background:rgba(255,255,255,0.15);
  border-radius:8px;
}
.overlay{
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(0,0,0,0.4);
  display:none;
  z-index:999;
}
.overlay.active{display:block;}
nav a {
  margin-left: 24px;
  text-decoration: none;
  font-weight: normal; /* ou 400 */
  color: var(--blue-dark);
  transition: color .2s;
}
nav a:hover{color:var(--blue);}

/* Main */
main {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 120px);
  padding: 20px;
  transition: margin-left 0.3s ease;
}
.sidebar.active ~ main { margin-left: 250px; }

/* Card */
.card {
  background: var(--white);
  border-radius: var(--radius);
  padding: 40px;
  box-shadow: 0 10px 25px rgba(20,40,80,0.06);
  width: 100%;
  max-width: 900px;
}
.input{
  width:100%;
  padding:10px;
  margin-bottom:12px;
  border-radius:8px;
  border:1px solid #ccc;
}
.btn-primary{
  padding:12px;
  background: var(--blue);
  color: #fff;
  border:none;
  border-radius:8px;
  cursor:pointer;
}
.btn-primary:hover{ background:#1d4e7a; }
.msg-error{color:var(--red)}
.msg-ok{color:green}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="overlay" id="overlay"></div>
  <div class="sidebar" id="sidebar">
  <ul>
    <li><a href="calendario.php">📅Calendário</a></li>
    <li><a href="ver_agendamentos.php">📝 Meus Agendamentos</a></li>
    <li><a href="perfil.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<header>
  <div class="logo-container">
    <div class="logo">
      <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar" id="logo-btn">
    </div>
    <div class="tooltip" id="tooltip"></div>
  </div>
  <nav>
    <a href="inicio.php"><strong>Home</strong></a>
  </nav>
</header>

<main>
<section class="card form">
  <h2>Agendar Recurso</h2>

  <?php if($message): ?>
    <p class="<?php echo empty($errors) ? 'msg-ok' : 'msg-error' ?>"><?php echo $message ?></p>
  <?php endif; ?>

  <form method="POST" id="form-agenda">
    <label>Recurso:</label>
    <select class="input" id="recurso" name="recurso" required>
      <option value="">-- Selecione o recurso --</option>
      <?php foreach($recursos_db as $r): ?>
        <option 
          value="<?php echo htmlspecialchars($r['nome']) ?>"
          data-tipo="<?php echo htmlspecialchars($r['tipo']) ?>"
          data-quantidade="<?php echo htmlspecialchars($r['quantidade_total'] ?? '') ?>"
        >
          <?php echo htmlspecialchars($r['nome']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <div id="chromebook-options" style="display:none; margin-top:8px;">
      <label>Ilha:</label>
      <select class="input" id="ilha" name="ilha">
        <option value="">-- Selecione a ilha --</option>
        <option value="1">Ilha 1 (34 Chromebooks)</option>
        <option value="2">Ilha 2 (35 Chromebooks)</option>
      </select>

      <label>Quantidade:</label>
      <input class="input" type="number" id="quantidade-chrome" name="quantidade" min="1" placeholder="Digite a quantidade">
      <div class="small-muted">Selecione a ilha para ver disponibilidade no horário.</div>
    </div>

    <div id="quantidade-options" style="display:none; margin-top:8px;">
      <label>Quantidade:</label>
      <input class="input" type="number" id="quantidade-outros" name="quantidade" min="1" placeholder="Digite a quantidade">
      <div class="small-muted" id="qtd-info"></div>
    </div>

    <label>Data:</label>
    <input class="input" type="date" id="data" name="data" required>

    <label>Horário / Aula:</label>
    <select class="input" id="horario" name="horario" required>
      <option value="">-- Selecione um horário --</option>
    </select>

    <div style="margin-top:12px">
      <button class="btn-primary" id="btn-submit" type="submit">Agendar</button>
    </div>
  </form>



<script>
// Sidebar toggle
const logoBtn = document.getElementById('logo-btn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
logoBtn.addEventListener('click', () => {
  sidebar.classList.toggle('active');
  overlay.classList.toggle('active');
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('active');
  overlay.classList.remove('active');
});

// Elementos
const recursoSelect = document.getElementById('recurso');
const dataInput = document.getElementById('data');
const horarioSelect = document.getElementById('horario');
const btnSubmit = document.getElementById('btn-submit');
const chromebookOptions = document.getElementById('chromebook-options');
const quantidadeOptions = document.getElementById('quantidade-options');
const ilhaSelect = document.getElementById('ilha');
const quantidadeChrome = document.getElementById('quantidade-chrome');
const quantidadeOutros = document.getElementById('quantidade-outros');
const qtdInfo = document.getElementById('qtd-info');

function isWeekend(dateStr){
    if(!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    const day = d.getDay();
    return (day === 0 || day === 6);
}

async function atualizarHorarios() {
    const recurso = recursoSelect.value;
    const data = dataInput.value;
    const ilha = ilhaSelect.value;

    horarioSelect.innerHTML = '<option value="">-- Carregando... --</option>';
    btnSubmit.disabled = true;

    if(!recurso || !data) {
        horarioSelect.innerHTML = '<option value="">-- Selecione recurso e data --</option>';
        return;
    }

    if(isWeekend(data)){
        horarioSelect.innerHTML = '<option value="">Finais de semana não são permitidos</option>';
        return;
    }

    try {
        let url = `get_horarios2.php?recurso=${encodeURIComponent(recurso)}&data=${encodeURIComponent(data)}`;
        if(recurso.toLowerCase().includes('chromebook')){
            if(!ilha){
                horarioSelect.innerHTML = '<option value="">-- Selecione a ilha para ver horários --</option>';
                btnSubmit.disabled = true;
                return;
            }
            url += `&ilha=${encodeURIComponent(ilha)}`;
        }
        const res = await fetch(url);
        if(!res.ok) throw new Error('erro na requisição');
        const arr = await res.json();

        horarioSelect.innerHTML = '';
        if(!arr || arr.length === 0){
            horarioSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
            btnSubmit.disabled = true;
        } else {
            horarioSelect.appendChild(new Option('-- Selecione um horário --',''));
            arr.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                horarioSelect.appendChild(opt);
            });
            btnSubmit.disabled = false;
        }
    } catch(e){
        console.error(e);
        horarioSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
        btnSubmit.disabled = true;
    }
}

// lógica de mostrar campos de quantidade/ilha baseado no recurso selecionado
recursoSelect.addEventListener('change', () => {
    const selected = recursoSelect.options[recursoSelect.selectedIndex];
    const tipo = selected ? selected.dataset.tipo : null;
    const qtd = selected ? selected.dataset.quantidade : null;
    const nome = selected ? selected.value : '';

    // reset visual
    chromebookOptions.style.display = 'none';
    quantidadeOptions.style.display = 'none';
    quantidadeChrome.disabled = true;
    quantidadeOutros.disabled = true;
    quantidadeChrome.value = '';
    quantidadeOutros.value = '';
    qtdInfo.textContent = '';

    // chromebook detectado por nome
    if(nome.toLowerCase().includes('chromebook')){
        chromebookOptions.style.display = 'block';
        quantidadeChrome.disabled = false;
    }
    else if(tipo === 'equipamento'){
        quantidadeOptions.style.display = 'block';
        quantidadeOutros.disabled = false;
        if(qtd) {
            qtdInfo.textContent = `Disponível no total: ${qtd}`;
            quantidadeOutros.max = parseInt(qtd);
        } else {
            qtdInfo.textContent = '';
            quantidadeOutros.removeAttribute('max');
        }
    }

    atualizarHorarios();
});

// reagir a mudanças de data/ilha
dataInput.addEventListener('change', atualizarHorarios);
ilhaSelect.addEventListener('change', atualizarHorarios);

// bloquear envio se horário inválido
document.getElementById('form-agenda').addEventListener('submit', function(e){
    if(btnSubmit.disabled){
        e.preventDefault();
        alert('Escolha um horário válido antes de enviar.');
    }
});
</script>

</body>
</html>
