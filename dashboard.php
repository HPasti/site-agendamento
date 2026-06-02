<?php
session_start();
require_once 'auth.php';
requireLogin(); // só entra logado
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Agenda Escolar</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* ====== Sidebar ====== */
    .sidebar {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background: #2b6fb3;
      color: #fff;
      transition: left 0.3s ease;
      padding-top: 60px;
      z-index: 1000;
      border-radius: 0 12px 12px 0;
    }
    .sidebar.active { left: 0; }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar ul li { padding: 15px 20px; }
    .sidebar ul li a {
      color: #fff; text-decoration: none; display: block; transition: background 0.2s;
    }
    .sidebar ul li a:hover {
      background: rgba(255,255,255,0.15); border-radius: 8px;
    }

    /* ====== Header ====== */
    .topbar {
      background:linear-gradient(180deg,var(--blue-light),#f6fbff 60%);
      color: #153a5b;
      padding: 16px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 6px 18px rgba(20,40,80,0.06);
      border-radius: 14px;
      margin: 20px;
      position: sticky;
      top: 12px;
      z-index: 50;
    }
    .logo {
      cursor: pointer;
      font-weight: 700;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #153a5b;
    }

    /* ====== Conteúdo ====== */
    .content {
      padding: 20px;
      margin-top: 20px;
      max-width: 1100px;
      margin-left: auto;
      margin-right: auto;
      text-align: center;
    }
    .cards {
      display: flex;
      justify-content: center;
      gap: 24px;
      flex-wrap: wrap;
      margin-top: 20px;
    }
    .card {
      background: #ffffff;
      border-radius: 12px;
      padding: 24px;
      width: 260px;
      box-shadow: 0 8px 20px rgba(20,40,80,0.04);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 30px rgba(20,40,80,0.07);
    }
    .card a {
      display: inline-block;
      margin-top: 12px;
      padding: 10px 18px;
      background: #2b6fb3;
      color: #ffffff;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all .16s ease;
    }
    .card a:hover {
      background: #153a5b;
      transform: translateY(-2px);
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

    .dashboard-intro {
      font-size: 22px;
      font-weight: 600;
      color: #153a5b;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <!-- Barra superior -->
  <div class="topbar">
    <div class="logo" id="logoBtn">
      <img src="imagens/AgendaEscolar-removebg.png" alt="Logo" style="width:150px;height:100px;border-radius:8px;display:inline-block;margin-right:10px;">
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <ul>
      <li><a href="dashboard.php">🏠 Dashboard</a></li>
      <li><a href="calendario.php">📅 Agendar</a></li>
      <li><a href="ver_agendamentos.php">📝 Meus Agendamentos</a></li>
      <li><a href="perfil.php">👤 Meu Perfil</a></li>
      <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
  </div>

  <!-- Overlay -->
  <div class="overlay" id="overlay"></div>

  <!-- Conteúdo -->
  <div class="content">
    <h1>
      Bem-vindo, 
      <?php echo isset($_SESSION['user_nome']) ? htmlspecialchars($_SESSION['user_nome']) : 'Usuário(a)'; ?> 👋
    </h1>
    <p>Aqui você pode gerenciar seus agendamentos de recursos escolares.</p>

    <div class="dashboard-intro">O que você vai agendar hoje?</div>

    <div class="cards">
      <div class="card">
        <h2>📅 Novo Agendamento</h2>
        <p>Agende Chromebooks, Tablets, Laboratório e mais.</p>
        <a href="bookings.php">Agendar</a>
      </div>
      <div class="card">
        <h2>📝 Meus Agendamentos</h2>
        <p>Consulte e gerencie seus agendamentos existentes.</p>
        <a href="ver_agendamentos.php">Ver agendamentos</a>
      </div>
    </div>
  </div>

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
