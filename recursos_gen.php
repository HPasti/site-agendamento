<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

// Buscar todos os recursos
$stmt = $pdo->query("SELECT id, nome, tipo, quantidade_total, criado_em, materias_restritas FROM recursos ORDER BY nome ASC");
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar disciplinas distintas do banco (para o seletor)
$stmtDisciplinas = $pdo->query("SELECT DISTINCT disciplina FROM horarios WHERE disciplina <> '' ORDER BY disciplina ASC");
$disciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gerenciar Recursos - Agenda Escolar</title>
<style>
:root {
  --blue-light:#e8f3ff;
  --blue:#2b6fb3;
  --blue-dark:#1a365d;
  --white:#ffffff;
  --red:#b00020;
  --gray:#666;
  --radius:14px;
  --max-width:1200px;
}
* { box-sizing:border-box; margin:0; padding:0; }
body {
  font-family:"Inter", Arial, sans-serif;
  background:var(--blue-light);
  color:var(--blue-dark);
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

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

/* Conteúdo */
.content {
  flex:1;
  padding:40px 8%;
  max-width:var(--max-width);
  margin:auto;
}
h1 { font-size:28px; margin-bottom:24px; text-align:center; color:#12304a; }
.vazio {
  text-align:center;
  background:var(--white);
  padding:24px;
  border-radius:var(--radius);
  box-shadow:0 4px 14px rgba(0,0,0,0.05);
  color:#555;
  font-size:16px;
}

/* Botões */
.btn, .btn-danger {
  border:none;
  border-radius:8px;
  padding:8px 14px;
  cursor:pointer;
  font-weight:600;
  transition:background .2s;
}
.btn { background:var(--blue); color:#fff; }
.btn:hover { background:#1d5e9b; }
.btn-danger { background:var(--red); color:#fff; }
.btn-danger:hover { background:#9b001a; }

/* Formulário de recursos */
.form-inline { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
input, select { padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-size:15px; }
#form-recurso { display:none; margin-top:20px; }
#campo-quantidade, #campo-materias { display:none; flex-direction:column; gap:10px; width:100%; }
.materias-box { display:flex; flex-wrap:wrap; gap:10px; background:#f8faff; border:1px solid #cbd9f1; border-radius:10px; padding:10px; max-height:200px; overflow-y:auto; }
.materia-item { background:#e7f0ff; border:1px solid #b8d2ff; border-radius:20px; padding:6px 14px; font-size:14px; cursor:pointer; user-select:none; transition:0.2s; }
.materia-item:hover { background:#cfe2ff; }
.materia-item.selected { background:var(--blue); color:#fff; border-color:var(--blue-dark); }

/* Cards de recursos */
.cards-container {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:20px;
  margin-top:20px;
}
.card {
  background:#fff;
  border-radius:var(--radius);
  box-shadow:0 6px 18px rgba(0,0,0,0.08);
  padding:20px;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  transition:transform 0.2s, box-shadow 0.2s;
}
.card:hover {
  transform:translateY(-4px);
  box-shadow:0 12px 24px rgba(0,0,0,0.15);
}
.card h3 {
  margin-bottom:12px;
  color:var(--blue-dark);
}
.card p { margin-bottom:8px; color:#555; font-size:14px; }
.card .actions { margin-top:12px; display:flex; gap:10px; }

/* Footer */
footer {
  margin-top:auto;
  text-align:center;
  padding:18px;
  background: var(--blue-dark);
  color: var(--white);
  font-size: 0.9rem;
}

@media (max-width: 520px){
  .content { padding:20px; }
  .materias-box { max-height:150px; }
  .actions { flex-direction:column; }
}
</style>

<script>
function toggleCampos() {
  const tipo = document.querySelector('select[name="tipo"]').value;
  document.getElementById('campo-quantidade').style.display = (tipo==='equipamento')?'block':'none';
  document.getElementById('campo-materias').style.display = (tipo==='ambiente')?'flex':'none';
}
function mostrarFormulario() {
  const form = document.getElementById('form-recurso');
  form.style.display='block';
  document.getElementById('btn-add').style.display='none';
}
function toggleMateria(btn) {
  btn.classList.toggle('selected');
  const checkbox = btn.querySelector('input');
  checkbox.checked = !checkbox.checked;
}

// Sidebar toggle
document.addEventListener("DOMContentLoaded", ()=>{
  const logoBtn = document.getElementById('logoBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  logoBtn.addEventListener('click',()=>{ sidebar.classList.toggle('active'); overlay.classList.toggle('active'); });
  overlay.addEventListener('click',()=>{ sidebar.classList.remove('active'); overlay.classList.remove('active'); });
});
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
    <li><a href="recursos_gen.php">💼 Recursos</a></li>
    <li><a href="mensagens.php">✉️ Mensagens dos Usuários</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>
<div class="overlay" id="overlay"></div>

<div class="content">
  <h1>📦 Gerenciar Recursos</h1>

  <button class="btn" id="btn-add" onclick="mostrarFormulario()">➕ Adicionar Recurso</button>

  <form id="form-recurso" class="form-inline" method="POST" action="salvar_recurso.php">
    <input type="text" name="nome" placeholder="Nome do recurso" required>
    <select name="tipo" onchange="toggleCampos()" required>
      <option value="">Selecione o tipo</option>
      <option value="equipamento">Equipamento</option>
      <option value="ambiente">Ambiente</option>
    </select>

    <div id="campo-quantidade">
      <input type="number" name="quantidade_total" placeholder="Quantidade total disponível" min="1">
    </div>

    <div id="campo-materias">
      <label><b>Quando estas matérias estiverem em aula, o ambiente não poderá ser agendado (OPCIONAL):</b></label>
      <div class="materias-box">
        <?php foreach($disciplinas as $disc): ?>
          <div class="materia-item" onclick="toggleMateria(this)">
            <input type="checkbox" name="materias_restritas[]" value="<?= htmlspecialchars($disc) ?>" hidden>
            <?= htmlspecialchars($disc) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn">Adicionar</button>
  </form>

  <?php if(empty($recursos)): ?>
    <div class="vazio">Nenhum recurso cadastrado.</div>
  <?php else: ?>
    <div class="cards-container">
      <?php foreach($recursos as $r): ?>
        <div class="card">
          <h3><?= htmlspecialchars($r['nome']) ?></h3>
          <p><b>Tipo:</b> <?= ucfirst($r['tipo']) ?></p>
          <p><b>Quantidade:</b> <?= $r['tipo']=='equipamento'?htmlspecialchars($r['quantidade_total']??'-'):'-' ?></p>
          <p><b>Matérias Restritas:</b> 
            <?php
              if(!empty($r['materias_restritas'])){
                $restritas = json_decode($r['materias_restritas'],true);
                echo (is_array($restritas)&&count($restritas)>0)?htmlspecialchars(implode(', ',$restritas)):'-';
              } else echo '-';
            ?>
          </p>
          <p><b>Criado em:</b> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['criado_em']))) ?></p>
          <div class="actions">
            <form action="editar_recurso.php" method="GET" style="display:inline;">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn" type="submit">Editar</button>
            </form>
            <form action="excluir_recurso.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este recurso?');">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger" type="submit">Excluir</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<footer>
  &copy; <?= date('Y') ?> Agenda Escolar. Todos os direitos reservados.
</footer>

</body>
</html>
