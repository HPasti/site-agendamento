<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

// Configuração de locale para meses em português
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

// View: day ou month
$view = $_GET['view'] ?? 'month';
$dia = $_GET['dia'] ?? date('Y-m-d');

// Horários fixos (mapa usado na visualização "dia")
$mapaHorarios = [
    1 => ['07:00','07:50'],
    2 => ['07:50','08:40'],
    3 => ['08:40','09:30'],
    4 => ['09:50','10:40'],
    5 => ['10:40','11:30'],
    6 => ['11:30','12:20'],
    7 => ['13:10','14:00']
];

// Paleta de cores e ícones
$paleta = [
  "Chromebook" => ["color" => "#2b6fb3", "icon" => "💻"],
  "Caneta 3D"  => ["color" => "#f39c12", "icon" => "🖊️"],
  "Óculos VR"  => ["color" => "#9b59b6", "icon" => "🕶️"],
  "Tablet"     => ["color" => "#27ae60", "icon" => "📱"],
  "Quadra"     => ["color" => "#e74c3c", "icon" => "🏀"],
  "Auditório"  => ["color" => "#34495e", "icon" => "🎤"],
  "LIED"       => ["color" => "#16a085", "icon" => "📚"],
  "Laboratório de Ciências" => ["color" => "#d35400", "icon" => "⚗️"]
];

// Limites para recursos com quantidade
$limites = [
  "Caneta 3D"=>20,
  "Óculos VR"=>20,
  "Tablet"=>20
];

// Buscar agendamentos
if($view==='day'){
  $stmt = $pdo->prepare("SELECT a.id, a.recurso, a.horario, a.quantidade, a.ilha, u.nome FROM agendamentos a JOIN usuarios u ON a.user_id=u.id WHERE a.data=:dia ORDER BY a.horario");
  $stmt->execute([':dia'=>$dia]);
  $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}else{
  $month = date('m', strtotime($dia));
  $year = date('Y', strtotime($dia));
  $stmt = $pdo->prepare("SELECT a.id, a.recurso, a.data, u.nome FROM agendamentos a JOIN usuarios u ON a.user_id=u.id WHERE MONTH(a.data)=:month AND YEAR(a.data)=:year");
  $stmt->execute([':month'=>$month, ':year'=>$year]);
  $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Dados auxiliares para mês
$inicioMes = date('w', strtotime(date('Y-m-01', strtotime($dia))));
$diasNoMes = date('t', strtotime($dia));
$anoMes = date('Y-m', strtotime($dia));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Calendário de Agendamentos</title>
<style>
:root{--azul:#2b6fb3;--azul-escuro:#153a5b;--branco:#fff;}
*{box-sizing:border-box}body{margin:0;font-family:Inter,Arial,sans-serif;background:#f6fbff;color:#16324a}

/* Topbar */
/* Topbar */
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 8%;
  background: #ffffff; /* ✅ header branco */
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

/* ===== Logo ===== */
.topbar .logo {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
}
.topbar .logo img {
  height: 120px;
  width: 200px;
  transition: transform 0.2s ease;
}
.topbar .logo:hover img {
  transform: scale(1.05);
}
.topbar .logo::after {
  content: "Abrir barra lateral";
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translate(-50%, 100%);
  background: #1a365d; /* ✅ cor de fundo alterada */
  color: #fff;
  font-size: 13px;
  padding: 6px 10px;
  border-radius: 8px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s, transform 0.2s;
  white-space: nowrap;
}
.topbar .logo:hover::after {
  opacity: 1;
  transform: translate(-50%, 110%);
}


nav a {
  margin-left: 24px;
  text-decoration: none;
  font-weight: 600;
  color: var(--blue-dark);
  transition: color 0.2s;
}
nav a:hover { color: var(--blue); }


/* Sidebar */
.sidebar{position:fixed;top:0;left:-260px;width:260px;height:100%;background:var(--azul-escuro);color:#fff;transition:left .28s;padding-top:80px;z-index:1000}
.sidebar.active{left:0}
.sidebar ul{list-style:none;padding:0;margin:0}
.sidebar ul li{padding:14px 20px}
.sidebar ul li a{color:#fff;text-decoration:none;display:block;font-weight:normal}
.sidebar ul li a:hover{background:rgba(255,255,255,0.12);border-radius:8px;padding-left:12px}
.overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:none;z-index:900}
.overlay.active{display:block}

/* Container calendário */
.calendar-container{max-width:1100px;margin:34px auto;padding:20px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(20,40,80,0.06)}
.calendar-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.calendar-header h2{margin:0;color:var(--azul-escuro);text-transform:capitalize}
.calendar-header .actions a{background:var(--azul);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none;margin-left:6px;font-weight:600}
.calendar-header .actions a:hover{background:var(--azul-escuro)}

/* Month view */
.week-head{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-bottom:6px;color:var(--azul-escuro);font-weight:600;text-align:center}
#monthView{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
.day-cell{background:#f9f9f9;border-radius:10px;min-height:90px;padding:10px;position:relative;cursor:pointer;transition:all .12s}
.day-cell:hover{background:#e8f4ff}
.day-number{font-weight:700;margin-bottom:6px}
.event-dot{width:9px;height:9px;border-radius:50%;position:absolute;bottom:8px;left:8px}

/* Day view */
.day-wrapper{display:flex}
.hours{width:140px;padding-right:12px;color:#6b7280}
.hour{height:60px;border-bottom:1px solid #eee;padding-top:6px;text-align:right;padding-right:6px;font-size:13px}
.day-col{flex:1;position:relative;border-left:1px solid #eee;background:linear-gradient(to bottom,#fafafa 1px,transparent 1px);background-size:100% 60px;min-height:430px;padding-left:6px}
.event{position:absolute;border-radius:10px;color:#fff;padding:8px 10px;font-size:13px;box-shadow:0 6px 18px rgba(20,40,80,0.08);cursor:pointer;overflow:hidden}
.event small{display:block;font-size:12px;opacity:.95}

/* Modal */
.modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;z-index:1001}
.modal.active{display:flex}
.modal-content{background:#fff;border-radius:14px;padding:24px;max-width:400px;width:90%;box-shadow:0 10px 30px rgba(20,40,80,0.2)}
.modal-content h3{margin-top:0;color:#1e3a8a}
.modal-content label{display:block;margin-top:12px;font-weight:500}
.modal-content select,input{width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid #e6f0fb}
.modal-content button{margin-top:16px;padding:12px 20px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:all .16s ease}
.modal-content button:hover{background:#1e3a8a;transform:translateY(-2px)}

#overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.3);
  display: none;
  z-index: 150;
}
#overlay.active {
  display: block;
}

</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
  <div class="logo" id="logoBtn">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Logo">
  </div>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="pagina_diretor.php">🏠 Início</a></li>
    <li><a href="recursos_gen.php">🛠️ Gerenciar Recursos</a></li>
    <li><a href="editar_horarios.php">⏰ Editar Horários</a></li>
    <li><a href="bookings2.php">📅 Novo Agendamento</a></li>
    <li><a href="agendamentos_diretor.php">📝 Gerenciar Agendamentos</a></li>
    <li><a href="recursos.php">💼 Recursos</a></li>
    <li><a href="mensagens.php">✉️ Mensagens dos Usuários</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>
<div class="overlay" id="overlay"></div>

<!-- Modal -->
<div class="modal" id="modal">
  <div class="modal-content">
    <h3>Agendar Recurso</h3>
    <form id="form-agenda" method="post" action="salvar_agenda.php">
      <input type="hidden" id="modal-dia" name="data">
      <input type="hidden" id="modal-horario" name="horario">
      <label>Recurso</label>
      <select id="modal-recurso" name="recurso" required>
        <option value="">-- Selecione --</option>
        <?php foreach(array_keys($paleta) as $r): ?>
          <option><?= $r ?></option>
        <?php endforeach; ?>
      </select>
      <div id="ilha-container" style="display:none;margin-top:8px;">
        <label>Chromebooks por Ilha</label>
        <div style="display:flex;gap:8px;">
          <div><label>Ilha 1 (34)</label><input type="number" name="ilha1_quant" min="0" max="34" value="0"></div>
          <div><label>Ilha 2 (35)</label><input type="number" name="ilha2_quant" min="0" max="35" value="0"></div>
        </div>
      </div>
      <label id="label-quant">Quantidade</label>
      <input type="number" id="quantidade-geral" name="quantidade" min="1" value="1">
      <button type="submit">Agendar</button>
    </form>
  </div>
</div>

<!-- Calendário -->
<div class="calendar-container">
  <div class="calendar-header">
    <h2><?= strftime('%d de %B de %Y', strtotime($dia)) ?></h2>
    <div class="actions">
      <?php if($view==='day'): ?>
        <a href="?dia=<?= date('Y-m-d', strtotime($dia.' -1 day')) ?>&view=day">← Anterior</a>
        <a href="?dia=<?= date('Y-m-d', strtotime($dia.' +1 day')) ?>&view=day">Próximo →</a>
      <?php else: ?>
        <a href="?dia=<?= date('Y-m-d', strtotime($dia.' -1 month')) ?>&view=month">← Mês anterior</a>
        <a href="?dia=<?= date('Y-m-d', strtotime($dia.' +1 month')) ?>&view=month">Próximo mês →</a>
      <?php endif; ?>
      <a href="?dia=<?= date('Y-m-d') ?>&view=month">📅 Mês</a>
      <a href="?dia=<?= date('Y-m-d') ?>&view=day">🕒 Dia</a>
    </div>
  </div>

  <?php if($view==='month'): ?>
    <div class="week-head">
      <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
    </div>
    <div id="monthView">
      <?php
      for($i=0;$i<$inicioMes;$i++) echo '<div></div>';
      for($d=1;$d<=$diasNoMes;$d++):
        $dataDia=$anoMes.'-'.str_pad($d,2,'0',STR_PAD_LEFT);
        $recursoDia=[];
        foreach($agendamentos as $a){
          if(isset($a['data']) && $a['data']===$dataDia) $recursoDia[]=$a['recurso'];
        }
      ?>
        <div class="day-cell" onclick="window.location='?view=day&dia=<?= $dataDia ?>'">
          <div class="day-number"><?= $d ?></div>
          <?php foreach(array_unique($recursoDia) as $idx=>$r): ?>
            <div class="event-dot" style="background:<?= htmlspecialchars($paleta[$r]['color'] ?? '#999') ?>; left: <?= 8+($idx*12) ?>px" title="<?= htmlspecialchars($r) ?>"></div>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
  <?php else: ?>
    <div class="day-wrapper">
      <div class="hours">
        <?php foreach($mapaHorarios as $k=>$h): ?>
          <div class="hour"><?= $k ?>ª aula &nbsp; (<?= $h[0] ?> - <?= $h[1] ?>)</div>
        <?php endforeach; ?>
      </div>
      <div class="day-col" id="dayCol">
        <?php
          $grouped = [];
          foreach($agendamentos as $a){
            $idx=(int)$a['horario']; if($idx<1) $idx=1;
            $grouped[$idx][]=$a;
          }
          foreach($mapaHorarios as $k=>$h):
            $top = ($k-1)*60;
            $aulas = $grouped[$k] ?? [];
            $count = max(1,count($aulas));
            $widthPercent = 100/$count;
            $index=0;
            foreach($aulas as $ev):
              $left=$index*$widthPercent;
              $widthCss="calc({$widthPercent}% - 8px)";
              $color=$paleta[$ev['recurso']]['color']??'#2563eb';
              $icon=$paleta[$ev['recurso']]['icon']??'';
        ?>
          <div class="event" style="top:<?= $top ?>px; left:<?= $left ?>%; width:<?= $widthCss ?>; background:<?= $color ?>;"
               onclick="alert('Recurso: <?= htmlspecialchars($ev['recurso']) ?>\nUsuário: <?= htmlspecialchars($ev['nome']) ?>\nQuantidade: <?= htmlspecialchars($ev['quantidade']) ?><?= isset($ev['ilha'])&&$ev['ilha']?'\nIlha: '.htmlspecialchars($ev['ilha']):'' ?>')">
            <strong style="display:block"><?= $icon ?> <?= htmlspecialchars($ev['recurso']) ?></strong>
            <small><?= htmlspecialchars($ev['nome']) ?></small>
          </div>
        <?php
              $index++;
            endforeach;
        ?>
            <div style="position:absolute;top:<?= $top ?>px;left:0;right:0;height:60px;cursor:pointer;" onclick="openModal(<?= $k ?>)"></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
// Sidebar
const logoBtn=document.getElementById('logoBtn');
const sidebar=document.getElementById('sidebar');
const overlay=document.getElementById('overlay');
logoBtn.addEventListener('click',()=>{sidebar.classList.toggle('active');overlay.classList.toggle('active');});
overlay.addEventListener('click',()=>{sidebar.classList.remove('active');overlay.classList.remove('active');});

// Modal
const modal=document.getElementById('modal');
const recursoSelect=document.getElementById('modal-recurso');
const ilhaContainer=document.getElementById('ilha-container');
const quantidadeGeral=document.getElementById('quantidade-geral');
function openModal(horario){
  modal.classList.add('active');
  document.getElementById('modal-dia').value='<?= $dia ?>';
  document.getElementById('modal-horario').value=horario;
  quantidadeGeral.value=1;
}
modal.addEventListener('click',e=>{if(e.target===modal) modal.classList.remove('active');});

recursoSelect.addEventListener('change',()=>{
  if(recursoSelect.value==='Chromebook'){
    ilhaContainer.style.display='block';
    quantidadeGeral.style.display='none';
  }else if(['Caneta 3D','Óculos VR','Tablet'].includes(recursoSelect.value)){
    ilhaContainer.style.display='none';
    quantidadeGeral.style.display='block';
    quantidadeGeral.max = <?= json_encode($limites) ?>[recursoSelect.value]||100;
  }else{
    ilhaContainer.style.display='none';
    quantidadeGeral.style.display='block';
    quantidadeGeral.removeAttribute('max');
  }
});
</script>
</body>
</html>
