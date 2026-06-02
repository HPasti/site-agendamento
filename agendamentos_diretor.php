<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();
$msg_sucesso = "";

// Exclusão de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['agendamento_id'])) {
    $id = (int)$_POST['agendamento_id'];
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$id]);
    $msg_sucesso = "Agendamento excluído com sucesso!";
}

// Totais
$agendamentos_total = $pdo->query("SELECT COUNT(*) FROM agendamentos")->fetchColumn();
$agendamentos_proximos = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data >= CURDATE()")->fetchColumn();

// Recursos
$stmt_recursos = $pdo->query("
    SELECT nome, tipo, COUNT(*) as quantidade
    FROM recursos
    GROUP BY nome, tipo
    ORDER BY nome
");
$recursos = $stmt_recursos->fetchAll(PDO::FETCH_ASSOC);

// Lista de recursos processada
$recursos_listados = [];
foreach ($recursos as $recurso) {
    if ($recurso['tipo'] === 'ambiente') {
        $recursos_listados[] = ['nome'=>$recurso['nome'], 'tipo'=>$recurso['tipo']];
    } else {
        for ($i=1; $i<=$recurso['quantidade']; $i++) {
            $recursos_listados[] = ['nome'=>$recurso['nome']." ($i)", 'tipo'=>$recurso['tipo']];
        }
    }
}

// Agendamentos
$agendamentos = $pdo->query("
    SELECT a.*, u.nome AS usuario_nome
    FROM agendamentos a
    JOIN usuarios u ON a.user_id = u.id
    ORDER BY a.data DESC, a.horario ASC
")->fetchAll(PDO::FETCH_ASSOC);

$HORARIOS = [
    1 => "1ª aula (07:00 - 07:50)",
    2 => "2ª aula (07:50 - 08:40)",
    3 => "3ª aula (08:40 - 09:30)",
    4 => "4ª aula (09:50 - 10:40)",
    5 => "5ª aula (10:40 - 11:30)",
    6 => "6ª aula (11:30 - 12:20)",
    7 => "7ª aula (13:10 - 14:00)",
];
function label_for_horario($id, $HORARIOS) { return $HORARIOS[$id] ?? "Aula $id"; }
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel do Diretor - Agenda Escolar</title>
<style>
:root {
  --blue-light: #e8f3ff;
  --blue: #2b6fb3;
  --blue-dark: #1a365d;
  --white: #ffffff;
  --green: #28a745;
  --red: #b00020;
  --gray: #666666;
  --radius: 14px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"Inter", Arial, sans-serif; background:var(--blue-light); color:var(--blue-dark); display:flex; flex-direction:column; min-height:100vh; }

/* Header */
.header { display:flex; justify-content:space-between; align-items:center; padding:16px 8%; background:var(--white); box-shadow:0 4px 12px rgba(0,0,0,0.05); position:sticky; top:0; z-index:100; }
.header .logo { position:relative; display:flex; align-items:center; gap:12px; cursor:pointer; }
.header .logo img { height:120px; transition:transform 0.2s; }
.header .logo:hover img { transform:scale(1.05); }

/* Tooltip da logo */
.logo::after {
  content: "Abrir barra lateral";
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translate(-50%, 100%);
  background: var(--blue-dark);
  color: var(--white);
  font-size: 13px;
  padding: 6px 10px;
  border-radius: 8px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s, transform 0.2s;
  white-space: nowrap;
}
.logo:hover::after {
  opacity: 1;
  transform: translate(-50%, 110%);
}

nav a { margin-left:24px; text-decoration:none; font-weight:600; color:var(--blue-dark); transition:color 0.2s; }
nav a:hover { color:var(--blue); }

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px;
  width: 260px;
  height: 100%;
  background: var(--blue-dark);
  color: var(--white);
  transition: left 0.3s ease;
  padding-top: 80px;
  z-index: 1000;
}
.sidebar.active { left: 0; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { padding: 15px 24px; }
.sidebar ul li a {
  color: var(--white);
  text-decoration: none;
  display: block;
  font-weight: 500;
  transition: background 0.2s;
}
.sidebar ul li a:hover {
  background: rgba(255,255,255,0.15);
  border-radius: 8px;
}
.overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  display: none;
  z-index: 999;
}
.overlay.active { display: block; }

/* Conteúdo */
.content { flex:1; padding:40px 8%; max-width:1400px; margin:auto; }
.content h1 { font-size:32px; margin-bottom:8px; font-weight:700; }
.content p { font-size:16px; color:var(--gray); margin-bottom:30px; }

.stats { display:flex; gap:20px; margin-bottom:40px; flex-wrap:wrap; }
.stat-card { flex:1; min-width:220px; background:var(--white); border-radius:var(--radius); padding:24px; box-shadow:0 4px 14px rgba(0,0,0,0.05); text-align:left; transition: transform .15s ease; }
.stat-card:hover { transform:translateY(-4px); }
.stat-card h2 { font-size:14px; text-transform:uppercase; color:var(--gray); margin-bottom:8px; letter-spacing:0.5px; }
.stat-card p { font-size:28px; font-weight:700; color:var(--blue-dark); }

table { width:100%; border-collapse:collapse; margin-top:20px; background:var(--white); border-radius:12px; overflow:hidden; box-shadow:0 4px 14px rgba(0,0,0,0.05); }
th, td { padding:12px 16px; border-bottom:1px solid #ddd; text-align:left; }
th { background-color:var(--blue); color:white; text-transform:uppercase; font-size:13px; letter-spacing:0.5px; }
td { color:var(--blue-dark); font-size:15px; }
tr:hover { background:#f0f8ff; }

.alert { padding:12px 20px; background:var(--green); color:#fff; border-radius:8px; margin-bottom:20px; }
footer { margin-top:auto; text-align:center; padding:18px; background:var(--blue-dark); color:var(--white); font-size:0.9rem; }
button { cursor:pointer; }
</style>
</head>
<body>

<header class="header">
  <div class="logo" id="logoBtn">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
  </div>
</header>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="pagina_diretor.php">🏠 Início</a></li>
    <li><a href="calendario_diretor.php">📅Calendário</a></li>
    <li><a href="recursos_gen.php">🛠️ Gerenciar Recursos</a></li>
    <li><a href="editar_horarios.php">⏰ Editar Horários</a></li>
    <li><a href="bookings2.php">➕ Novo Agendamento</a></li>
    <li><a href="recursos_gen.php">💼 Recursos</a></li>
    <li><a href="mensagens.php">✉️ Mensagens dos Usuários</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<div class="content">
  <h1>Olá, <?= htmlspecialchars(ucwords($_SESSION['user_nome'])) ?> 👨‍🏫</h1>
  <p>Bem-vindo ao painel administrativo. Veja os agendamentos e gerencie os recursos da escola.</p>

  <?php if($msg_sucesso): ?>
    <div class="alert"><?= htmlspecialchars($msg_sucesso) ?></div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat-card">
      <h2>Agendamentos Totais</h2>
      <p><?= $agendamentos_total ?></p>
    </div>
    <div class="stat-card">
      <h2>Próximos Agendamentos</h2>
      <p><?= $agendamentos_proximos ?></p>
    </div>
    <div class="stat-card">
      <h2>Tipos de Recursos</h2>
      <p><?= count($recursos_listados) ?></p>
    </div>
  </div>

  <h2 style="color:#2b6fb3; margin-top:40px;">📋 Agendamentos Recentes</h2>
  <?php if(empty($agendamentos)): ?>
    <p style="text-align:center; color:#666; margin-top:30px;">Nenhum agendamento registrado.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Professor</th>
          <th>Recurso</th>
          <th>Data</th>
          <th>Horário</th>
          <th>Ilha</th>
          <th>Quantidade</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($agendamentos as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['usuario_nome']) ?></td>
          <td><?= htmlspecialchars($a['recurso']) ?></td>
          <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['data']))) ?></td>
          <td><?= htmlspecialchars(label_for_horario($a['horario'], $HORARIOS)) ?></td>
          <td><?= htmlspecialchars($a['ilha'] ?? '-') ?></td>
          <td><?= htmlspecialchars($a['quantidade'] ?? '1') ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Deseja realmente excluir este agendamento?');">
              <input type="hidden" name="agendamento_id" value="<?= $a['id'] ?>">
              <button type="submit" style="padding:6px 12px; background:#b00020; color:#fff; border:none; border-radius:6px;">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<footer>
  &copy; <?= date('Y'); ?> Agenda Escolar. Todos os direitos reservados.
</footer>

<script>
const logoBtn = document.getElementById('logoBtn');
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
</script>

</body>
</html>
