<?php
session_start();
require_once 'auth.php';
requireLogin();
$pdo = getPDO();

// Exclusão de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND user_id = ?");
    $del->execute([$_POST['delete_id'], $_SESSION['user_id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Buscar agendamentos do usuário logado
$bookings = $pdo->prepare('
    SELECT a.*, u.nome 
    FROM agendamentos a 
    JOIN usuarios u ON a.user_id = u.id 
    WHERE a.user_id = ? 
    ORDER BY a.data DESC
');
$bookings->execute([$_SESSION['user_id']]);
$my = $bookings->fetchAll(PDO::FETCH_ASSOC);

// Lista de horários
$HORARIOS = [
    1 => "1ª aula (07:00 - 07:50)",
    2 => "2ª aula (07:50 - 08:40)",
    3 => "3ª aula (08:40 - 09:30)",
    4 => "4ª aula (09:50 - 10:40)",
    5 => "5ª aula (10:40 - 11:30)",
    6 => "6ª aula (11:30 - 12:20)",
    7 => "7ª aula (13:10 - 14:00)",
];

function label_for_horario($id, $HORARIOS) {
    return $HORARIOS[$id] ?? "Aula $id";
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meus Agendamentos</title>
<style>
:root {
  --blue: #2b6fb3;
  --blue-dark: #1a365d;
  --white: #ffffff;
}

/* Layout base */
body {
  font-family: Arial, sans-serif;
  background-color: #e9f2ffff;
  margin: 0;
  padding: 0;
}

header {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 80px;
  padding: 18px 8%;
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

/* Navegação */
nav {
  display: flex;
  gap: 40px;
  margin-left: auto;
  margin-right: 80px;
}

nav a {
  text-decoration: none;
  font-weight: 600;
  color: var(--blue-dark);
  transition: color 0.2s;
}

nav a:hover { color: var(--blue); }

/* Conteúdo principal */
main {
  padding: 40px 8%;
}

.card {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

h2 { color: #1a365d; }

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th, td {
  padding: 10px 14px;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #2b6fb3;
  color: white;
  text-align: left;
}

td { color: #333; }

.msg-vazio {
  color: #666;
  text-align: center;
  margin-top: 40px;
  font-style: italic;
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

/* Botão vermelho "Excluir" */
.delete-btn {
  background: #e63946; /* vermelho vivo */
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 14px;
  font-weight: 600;
  margin-left: 10px;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}
.delete-btn:hover {
  background: #b91c1c;
  transform: scale(1.05);
}
</style>
</head>
<body>

<header>
  <div class="logo" id="logo">
    <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
  </div>
  <nav>
    <a href="inicio.php">Home</a>
    <a href="bookings.php">Novo Agendamento</a>
  </nav>
</header>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="inicio.php">🏠 Início</a></li>
    <li><a href="bookings.php">➕ Novo Agendamento</a></li>
    <li><a href="perfil.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<div class="overlay" id="overlay"></div>

<main>
  <section class="card">
    <h2>Meus Agendamentos</h2>

    <?php if(empty($my)): ?>
      <p class="msg-vazio">Você ainda não possui agendamentos.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Recurso</th>
            <th>Data</th>
            <th>Horário</th>
            <th>Ilha</th>
            <th>Quantidade</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($my as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['recurso']) ?></td>
            <td><?= htmlspecialchars($b['data']) ?></td>
            <td><?= htmlspecialchars(label_for_horario($b['horario'], $HORARIOS)) ?></td>
            <td><?= htmlspecialchars($b['ilha'] ?? '-') ?></td>
            <td>
              <?= htmlspecialchars($b['quantidade'] ?? '1') ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
                <button type="submit" class="delete-btn" title="Excluir agendamento" onclick="return confirm('Tem certeza que deseja excluir este agendamento?')">Excluir</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</main>

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
