<?php
session_start();
require_once 'auth.php';
requireDirector();

$pdo = getPDO();

$user_id = $_SESSION['user_id'];

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT nome, email, escola, criado FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  die("Usuário não encontrado.");
}

// Excluir conta
if (isset($_POST['delete_account'])) {
    $stmt_delete = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt_delete->execute([$user_id]);

    session_destroy();
    header("Location: index.php?msg=conta_excluida");
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meu Perfil - Agenda Escolar</title>
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

    nav a {
      margin-left: 24px;
      text-decoration: none;
      font-weight: 600;
      color: var(--blue-dark);
    }
    nav a:hover { color: var(--blue); }

    .content {
      flex: 1;
      padding: 40px 8%;
      max-width: 800px;
      margin: auto;
    }

    h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }

    .profile-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 28px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.06);
      margin-bottom: 30px;
    }
    .profile-card h2 {
      font-size: 20px;
      margin-bottom: 16px;
    }
    .profile-card p {
      margin-bottom: 8px;
      color: var(--gray);
    }
    .profile-card strong {
      color: var(--blue-dark);
    }

    .danger-zone {
      background: #fff5f5;
      border: 1px solid #ffcccc;
      border-radius: var(--radius);
      padding: 20px;
      margin-top: 30px;
    }
    .danger-zone h3 {
      color: var(--red);
      margin-bottom: 10px;
    }
    .danger-zone p {
      color: var(--gray);
      margin-bottom: 14px;
    }
    .danger-zone button {
      background: var(--red);
      color: var(--white);
      padding: 10px 20px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.2s;
    }
    .danger-zone button:hover {
      background: #7a0015;
    }

    footer {
      margin-top: auto;
      text-align: center;
      padding: 18px;
      background: var(--blue-dark);
      color: var(--white);
      font-size: 0.9rem;
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
  </style>
</head>
<body>

<header class="header">
  <div class="logo" id="logo">
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
    <li><a href="agendamentos_diretor.php">📝 Gerenciar Agendamentos</a></li>
    <li><a href="recursos_gen.php">💼 Recursos</a></li>
    <li><a href="mensagens.php">✉️ Mensagens dos Usuários</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<div class="overlay" id="overlay"></div>

<div class="content">
  <h1>Meu Perfil</h1>

  <div class="profile-card">
    <h2>Dados Pessoais</h2>
    <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
    <p><strong>Escola:</strong> <?php echo htmlspecialchars($usuario['escola']); ?></p>
    <p><strong>Conta criada em:</strong> <?php echo date("d/m/Y H:i", strtotime($usuario['criado'])); ?></p>
  </div>

  <div class="danger-zone">
    <h3>⚠ Zona de Perigo</h3>
    <p>Excluir sua conta é <strong>irreversível</strong>. Todos os seus agendamentos também serão apagados permanentemente.</p>
    <form method="POST" 
          onsubmit="return confirm('⚠ Tem certeza que deseja apagar sua conta? Essa ação é irreversível e todos os seus agendamentos serão excluídos!');">
      <button type="submit" name="delete_account">Excluir Minha Conta</button>
    </form>
  </div>
</div>

<footer>
  &copy; <?php echo date('Y'); ?> Agenda Escolar. Todos os direitos reservados.
</footer>

<script>
  const logo = document.getElementById('logo');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  logo.addEventListener('click', () => {
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
