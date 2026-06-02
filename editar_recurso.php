<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

// Verificar se veio um ID
if (!isset($_GET['id'])) {
    header("Location: recursos_gen.php");
    exit;
}

$id = (int)$_GET['id'];

// Buscar dados do recurso
$stmt = $pdo->prepare("SELECT * FROM recursos WHERE id = ?");
$stmt->execute([$id]);
$recurso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recurso) {
    echo "<p>Recurso não encontrado.</p>";
    exit;
}

// Buscar disciplinas distintas do banco (para o seletor)
$stmtDisciplinas = $pdo->query("SELECT DISTINCT disciplina FROM horarios WHERE disciplina <> '' ORDER BY disciplina ASC");
$disciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_COLUMN);

// Converter matérias restritas existentes (JSON → array)
$materias_restritas = !empty($recurso['materias_restritas'])
    ? json_decode($recurso['materias_restritas'], true)
    : [];

// Atualizar recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $tipo = $_POST['tipo'];
    $quantidade_total = ($tipo === 'equipamento') ? (int)$_POST['quantidade_total'] : null;
    $materias_restritas_post = isset($_POST['materias_restritas']) ? $_POST['materias_restritas'] : [];
    $materias_json = json_encode($materias_restritas_post, JSON_UNESCAPED_UNICODE);

    $update = $pdo->prepare("UPDATE recursos SET nome = ?, tipo = ?, quantidade_total = ?, materias_restritas = ? WHERE id = ?");
    $update->execute([$nome, $tipo, $quantidade_total, $materias_json, $id]);

    header("Location: recursos_gen.php");
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Editar Recurso</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>

  /* Header */
.header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:16px 8%;
  background:var(--white);
  box-shadow:0 4px 12px rgba(0,0,0,0.05);
  position:sticky;
  top:0;
  z-index:100;
}
.header .logo{
  position:relative;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:12px;
}
.header .logo img{height:120px;transition:transform .2s;}
.header .logo:hover img{transform:scale(1.05);}

/* Sidebar */
.sidebar {
  position: fixed;
  top:0;
  left:-260px;
  width:260px;
  height:100%;
  background:var(--blue-dark);
  color:var(--white);
  transition:left 0.3s ease;
  padding-top:80px;
  z-index:1000;
}
.sidebar.active { left:0; }
.sidebar ul { list-style:none; padding:0; }
.sidebar ul li { padding:15px 24px; }
.sidebar ul li a {
  color:var(--white);
  text-decoration:none;
  display:block;
  font-weight:500;
  transition:background 0.2s;
}
.sidebar ul li a:hover {
  background:rgba(255,255,255,0.15);
  border-radius:8px;
}
.overlay {
  position:fixed;
  top:0; left:0;
  width:100%; height:100%;
  background:rgba(0,0,0,0.4);
  display:none;
  z-index:999;
}
.overlay.active { display:block; }
:root {
  --blue-dark:#153a5b;
  --blue:#2b6fb3;
  --blue-light:#d9ecff;
  --white:#ffffff;
  --max-width:800px;
}
body {
  margin:0;
  font-family:Inter,Arial,sans-serif;
  background:linear-gradient(180deg,var(--blue-light),#f6fbff 60%);
  color:var(--blue-dark);
  display:flex;
  flex-direction:column;
  min-height:100vh;
}
header {
  padding:18px 8%;
  display:flex;
  justify-content:center;
  align-items:center;
  position:relative;
}
header h1 { margin:0; text-align:center; }
header a {
  color:#fff;
  text-decoration:none;
  font-weight:600;
  position:absolute;
  right:80px;
}
.container {
  flex:1;
  max-width:var(--max-width);
  margin:40px auto;
  background:var(--white);
  border-radius:14px;
  box-shadow:0 8px 20px rgba(0,0,0,0.1);
  padding:30px;
}
form {
  display:flex;
  flex-direction:column;
  gap:15px;
}
label {
  font-weight:600;
}
input,select {
  padding:10px;
  border:1px solid #ccc;
  border-radius:8px;
  font-size:15px;
}
.btn {
  background:var(--blue);
  color:#fff;
  border:none;
  padding:10px 14px;
  border-radius:8px;
  cursor:pointer;
  transition:0.2s;
  font-weight:600;
  width:fit-content;
}
.btn:hover { background:#1d5e9b; }

#campo-quantidade, #campo-materias {
  display:none;
  flex-direction:column;
  gap:10px;
  width:100%;
}
.materias-box {
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  background:#f8faff;
  border:1px solid #cbd9f1;
  border-radius:10px;
  padding:10px;
  max-height:200px;
  overflow-y:auto;
}
.materia-item {
  background:#e7f0ff;
  border:1px solid #b8d2ff;
  border-radius:20px;
  padding:6px 14px;
  font-size:14px;
  cursor:pointer;
  user-select:none;
  transition:0.2s;
}
.materia-item:hover {
  background:#cfe2ff;
}
.materia-item.selected {
  background:var(--blue);
  color:#fff;
  border-color:var(--blue-dark);
}
</style>
<script>
function toggleCampos() {
  const tipo = document.querySelector('select[name="tipo"]').value;
  document.getElementById('campo-quantidade').style.display = (tipo === 'equipamento') ? 'block' : 'none';
  document.getElementById('campo-materias').style.display = (tipo === 'ambiente') ? 'flex' : 'none';
}
function toggleMateria(btn) {
  btn.classList.toggle('selected');
  const checkbox = btn.querySelector('input');
  checkbox.checked = !checkbox.checked;
}
</script>
</head>
<body>

<header class="header">
  <div class="logo" id="logoBtn">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
  </div>
</header>

<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="pagina_diretor.php">🏠 Início</a></li>
    <li><a href="calendario_diretor.php">📅 Calendário</a></li>
    <li><a href="editar_horarios.php">⏰ Editar Horários</a></li>
    <li><a href="bookings2.php">➕ Novo Agendamento</a></li>
    <li><a href="agendamentos_diretor.php">📝 Gerenciar Agendamentos</a></li>
    <li><a href="recursos_gen.php">💼 Recursos (Voltar)</a></li>
    <li><a href="mensagens.php">✉️ Mensagens dos Usuários</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>
<div class="overlay" id="overlay"></div>

<header>
  <h1>✏️ Editar Recurso</h1>
</header>

<div class="container">
  <form method="POST">
    <label>Nome do Recurso:</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($recurso['nome']) ?>" required>

    <label>Tipo:</label>
    <select name="tipo" onchange="toggleCampos()" required>
      <option value="">Selecione o tipo</option>
      <option value="equipamento" <?= $recurso['tipo'] === 'equipamento' ? 'selected' : '' ?>>Equipamento</option>
      <option value="ambiente" <?= $recurso['tipo'] === 'ambiente' ? 'selected' : '' ?>>Ambiente</option>
    </select>

    <div id="campo-quantidade" style="display: <?= $recurso['tipo'] === 'equipamento' ? 'block' : 'none' ?>;">
      <label>Quantidade Total Disponível:</label>
      <input type="number" name="quantidade_total" min="1" value="<?= htmlspecialchars($recurso['quantidade_total']) ?>">
    </div>

    <div id="campo-materias" style="display: <?= $recurso['tipo'] === 'ambiente' ? 'flex' : 'none' ?>;">
      <label><b>Quando estas matérias estiverem em aula, o ambiente não poderá ser agendado:</b></label>
      <div class="materias-box">
        <?php foreach($disciplinas as $disc): 
          $selecionada = in_array($disc, $materias_restritas);
        ?>
          <div class="materia-item <?= $selecionada ? 'selected' : '' ?>" onclick="toggleMateria(this)">
            <input type="checkbox" name="materias_restritas[]" value="<?= htmlspecialchars($disc) ?>" <?= $selecionada ? 'checked' : '' ?> hidden>
            <?= htmlspecialchars($disc) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn">Salvar Alterações</button>
  </form>
</div>

</body>
</html>

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