<?php
session_start();
require_once 'auth.php';
requireDirector();

$user_id = $_SESSION['user_id'];
$pdo = getPDO();



// Totais de agendamentos
$stmt_total = $pdo->query("SELECT COUNT(*) FROM agendamentos");
$agendamentos_total = $stmt_total->fetchColumn();

$stmt_proximos = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data >= CURDATE()");
$agendamentos_proximos = $stmt_proximos->fetchColumn();

// Consulta recursos (todos)
$stmt_recursos = $pdo->query("
  SELECT nome, tipo, COUNT(*) as quantidade 
  FROM recursos 
  GROUP BY nome, tipo 
  ORDER BY nome
");
$recursos = $stmt_recursos->fetchAll(PDO::FETCH_ASSOC);

// Processar nomes duplicados com numeração (exceto ambientes)
$recursos_listados = [];
foreach ($recursos as $recurso) {
  if ($recurso['tipo'] === 'ambiente') {
    $recursos_listados[] = [
      'nome' => $recurso['nome'],
      'tipo' => $recurso['tipo']
    ];
  } else {
    for ($i = 1; $i <= $recurso['quantidade']; $i++) {
      $recursos_listados[] = [
        'nome' => $recurso['nome'] . " ($i)",
        'tipo' => $recurso['tipo']
      ];
    }
  }
}

// Contar mensagens não lidas
$stmt_nao_lidas = $pdo->query("SELECT COUNT(*) FROM contatos WHERE lida = 0");
$mensagens_nao_lidas = $stmt_nao_lidas->fetchColumn();
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard do Diretor - Agenda Escolar</title>
  <style>
    :root {
      --blue-light: #e8f3ff;
      --blue: #2b6fb3;
      --blue-dark: #1a365d;
      --white: #ffffff;
      --red: #b00020;
      --gray: #666666;
      --radius: 14px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: "Inter", Arial, sans-serif;
      background: var(--blue-light);
      color: var(--blue-dark);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 8%;
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
.header .logo img{height:120px;transition:transform .2s;}
.header .logo:hover img{transform:scale(1.05);}
.header .logo::after{
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
.header .logo:hover::after{
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
.sidebar ul li a{
  color:var(--white);
  text-decoration:none;
  display:block;
  font-weight:500;
  transition:background .2s;
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

nav a{
  margin-left:24px;
  text-decoration:none;
  font-weight:600;
  color:var(--blue-dark);
  transition:color .2s;
}
nav a:hover{color:var(--blue);}

    .content {
      flex: 1;
      padding: 40px 8%;
      max-width: 1400px;
      margin: auto;
    }
    .content h1 {
      font-size: 32px;
      margin-bottom: 8px;
      font-weight: 700;
    }
    .content p {
      font-size: 16px;
      color: var(--gray);
      margin-bottom: 30px;
    }

    .stats {
      display: flex;
      gap: 20px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    .stat-card {
      flex: 1;
      min-width: 220px;
      background: var(--white);
      border-radius: var(--radius);
      padding: 24px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.05);
      text-align: left;
      transition: transform .15s ease;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card h2 {
      font-size: 14px;
      text-transform: uppercase;
      color: var(--gray);
      margin-bottom: 8px;
      letter-spacing: 0.5px;
    }
    .stat-card p {
      font-size: 28px;
      font-weight: 700;
      color: var(--blue-dark);
    }

    .cards {
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 40px;
    }

    .card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 28px;
      width: 300px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.06);
      transition: transform .18s ease, box-shadow .18s ease;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    .card h2 {
      margin-bottom: 12px;
      font-size: 20px;
      color: var(--blue-dark);
    }
    .card p {
      font-size: 15px;
      margin-bottom: 18px;
      color: var(--gray);
    }
    .card a {
      display: inline-block;
      padding: 10px 18px;
      background: var(--blue);
      color: var(--white);
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all .16s ease;
    }
    .card a:hover {
      background: var(--blue-dark);
      transform: translateY(-2px);
    }

    .badge {
      background:#b00020; 
      color:#fff; 
      padding:2px 8px; 
      border-radius:50%; 
      margin-left:8px; 
      font-size:14px;
    }

    footer {
      margin-top: auto;
      text-align: center;
      padding: 18px;
      background: var(--blue-dark);
      color: var(--white);
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<header class="header">
  <div class="logo" id="logoBtn">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
  </div>
  <nav>
    <a href="logout.php">Sair</a>
  </nav>
  
</header>

<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="pagina_diretor.php">🏠 Início</a></li>
    <li><a href="calendario_diretor.php">📅Calendário</a></li>
    <li><a href="recursos_gen.php">🛠️ Gerenciar Recursos</a></li>
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
  <h1>Olá, <?php echo htmlspecialchars(ucwords($_SESSION['user_nome'])); ?> 👨‍🏫</h1>
  <p>Gerencie recursos, horários e supervisione os agendamentos da escola.</p>

  <div class="stats">
    <div class="stat-card">
      <h2>Agendamentos Totais</h2>
      <p><?php echo $agendamentos_total; ?></p>
    </div>
    <div class="stat-card">
      <h2>Próximos Agendamentos</h2>
      <p><?php echo $agendamentos_proximos; ?></p>
    </div>
    <div class="stat-card">
      <h2>Tipos de Recursos</h2>
      <p><?php echo count($recursos_listados); ?></p>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <h2>📅 Novo Agendamento</h2>
      <p>Agende Chromebooks, Tablets, Laboratório e mais.</p>
      <a href="bookings2.php">Agendar</a>
    </div>
    <div class="card">
      <h2>📝 Gerenciar Agendamentos</h2>
      <p>Consulte e gerencie seus agendamentos.</p>
      <a href="agendamentos_diretor.php">Ver agendamentos</a>
    </div>
    <div class="card">
      <h2>👤 Meu Perfil</h2>
      <p>Veja e atualize suas informações pessoais.</p>
      <a href="perfil2.php">Acessar</a>
    </div>
  </div>

  <!-- Recursos, Mensagens e Horários com espaçamento correto -->
  <div class="cards">
    <div class="card">
      <h2>📦 Recursos Cadastrados</h2>
      <p>Gerencie todos os recursos cadastrados na escola.</p>
      <a href="recursos_gen.php">Gerenciar Recursos</a>
    </div>
    <div class="card">
      <h2>💬 Gerenciar Mensagens</h2>
      <p>Visualize e responda mensagens enviadas pelos usuários.</p>
      <a href="mensagens.php">Ver Mensagens</a>
      <?php if($mensagens_nao_lidas > 0): ?>
        <span class="badge"><?= $mensagens_nao_lidas ?></span>
      <?php endif; ?>
    </div>
    <div class="card">
      <h2>⏰ Gerenciar Horários</h2>
      <p>Edite horários disponíveis para agendamento.</p>
      <a href="editar_horarios.php">Editar Horários</a>
    </div>
  </div>
</div>

<footer>
  &copy; <?php echo date('Y'); ?> Agenda Escolar. Todos os direitos reservados.
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
