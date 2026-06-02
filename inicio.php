<?php
session_start();
require_once 'auth.php';
requireLogin();

$pdo = getPDO();

// ID do usuário logado
$user_id = $_SESSION['user_id'];

// Agendamentos Totais do usuário
$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE user_id = ?");
$stmt_total->execute([$user_id]);
$agendamentos_total = $stmt_total->fetchColumn();

// Próximos agendamentos (hoje ou futuro)
$stmt_proximos = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE user_id = ? AND data >= CURDATE()");
$stmt_proximos->execute([$user_id]);
$agendamentos_proximos = $stmt_proximos->fetchColumn();

// Recursos disponíveis (contagem real do banco)
$recursos_disponiveis = $pdo->query("SELECT COUNT(*) FROM recursos")->fetchColumn();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Agenda Escolar</title>
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

    /* Header */
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
    .header .logo {
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .header .logo img { height: 120px; }
    nav a {
      margin-left: 24px;
      text-decoration: none;
      font-weight: 600;
      color: var(--blue-dark);
      transition: color 0.2s;
    }
    nav a:hover { color: var(--blue); }

    /* Tooltip da logo */
.logo {
  position: relative;
  cursor: pointer;
}

.logo img {
  height: 120px;
  transition: transform 0.2s;
}

.logo:hover img {
  transform: scale(1.05);
}

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

    /* Cards Estatísticos */
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

    /* Cards de ação */
    .cards {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 28px;
      width: 300px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.06);
      transition: transform .18s ease, box-shadow .18s ease;
      text-align: center;
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
      font-size: 14px;
      margin-bottom: 16px;
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

<!-- Header -->
<header class="header">
  <div class="logo" id="logoBtn">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
  </div>
  <nav>
    <a href="logout.php">Sair</a>
  </nav>
</header>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="calendario.php">📅Calendário</a></li>
    <li><a href="ver_agendamentos.php">📝 Meus Agendamentos</a></li>
    <li><a href="perfil.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Conteúdo -->
<div class="content">
  <!-- 🔹 Nome sempre com a primeira letra maiúscula -->
  <h1>Bem-vindo(a), <?php echo htmlspecialchars(ucwords($_SESSION['user_nome'])); ?> 👋</h1>
  <p>Gerencie seus recursos escolares de forma simples, rápida e organizada.</p>

  <!-- Estatísticas -->
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
      <h2>Recursos Disponíveis</h2>
      <p><?php echo $recursos_disponiveis; ?></p>
    </div>
  </div>

  <!-- Ações -->
  <div class="cards">
    <div class="card">
      <h2>📅 Novo Agendamento</h2>
      <p>Agende Chromebooks, Tablets, Laboratório e mais.</p>
      <a href="bookings.php">Agendar</a>
    </div>
    <div class="card">
      <h2>📝 Meus Agendamentos</h2>
      <p>Consulte e gerencie seus agendamentos.</p>
      <a href="ver_agendamentos.php">Ver agendamentos</a>
    </div>
    <div class="card">
      <h2>👤 Meu Perfil</h2>
      <p>Veja e atualize suas informações pessoais.</p>
      <a href="perfil.php">Acessar</a>
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
