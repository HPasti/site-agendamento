<?php
session_start();
require_once 'auth.php';
requireDirector();

$pdo = getPDO();

// Marcar mensagem como lida
if (isset($_GET['marcar_lida'])) {
    $id = (int)$_GET['marcar_lida'];
    $stmt = $pdo->prepare("UPDATE contatos SET lida = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: mensagens.php");
    exit;
}

// Excluir mensagem
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM contatos WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: mensagens.php");
    exit;
}

// Buscar mensagens
$stmt = $pdo->query("SELECT * FROM contatos ORDER BY data_envio DESC");
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar mensagens não lidas
$stmt_nao_lidas = $pdo->query("SELECT COUNT(*) FROM contatos WHERE lida = 0");
$mensagens_nao_lidas = $stmt_nao_lidas->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mensagens dos Usuários - Agenda Escolar</title>
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

    * { box-sizing: border-box; margin: 0; padding: 0; }

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
.header .logo{
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

    /* Conteúdo principal */
    .content {
      flex: 1;
      padding: 40px 8%;
      max-width: 1200px;
      margin: auto;
    }

    h1 {
      font-size: 28px;
      margin-bottom: 24px;
      color: #12304a;
      text-align: center;
    }

    .mensagens-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 20px;
      max-width: 1000px;
      margin: 0 auto;
      width: 100%;
      padding: 0 20px;
    }

    .card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: 0 8px 30px rgba(10,36,70,0.06);
      padding: 20px 22px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      transition: transform 0.15s ease;
      width: 100%;
    }
    .card:hover { transform: translateY(-4px); }

    .info {
      display: flex;
      flex-wrap: wrap;
      gap: 18px 30px;
      font-size: 15px;
      align-items: flex-start;
    }
    .info > div {
      flex: 1 1 160px;
      min-width: 160px;
    }
    .info span {
      display: block;
      color: #175f9a;
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 6px;
    }

    .mensagem {
      background: #f7f9fc;
      padding: 14px;
      border-radius: 10px;
      line-height: 1.5;
      font-size: 15px;
      color: #333;
      white-space: pre-wrap;
      margin-top: 6px;
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 6px;
      gap: 10px;
    }
    .data {
      font-size: 13px;
      color: #666;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-excluir, .btn-lida, .add-button {
      border: none;
      border-radius: 8px;
      padding: 8px 12px;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      transition: background 0.2s ease;
      margin-left: 8px;
    }
    .btn-excluir { background: #b00020; color: #fff; }
    .btn-excluir:hover { background: #9b001a; }
    .btn-lida { background: #2b6fb3; color: #fff; }
    .btn-lida:hover { background: #1a4f8a; }
    .add-button { background: #1a365d; color: #fff; }
    .add-button:hover { background: #10294a; }

    .vazio {
      text-align: center;
      background: #fff;
      padding: 24px;
      border-radius: var(--radius);
      box-shadow: 0 4px 14px rgba(0,0,0,0.05);
      color: #555;
      font-size: 16px;
    }

    footer {
      margin-top: auto;
      text-align: center;
      padding: 18px;
      background: var(--blue-dark);
      color: var(--white);
      font-size: 0.9rem;
    }

    @media (max-width: 520px) {
      .content { padding: 20px; }
      .mensagens-container { padding: 0 8px; }
      .info > div { flex-basis: 100%; min-width: 100%; }
      .card { padding: 16px; }
      .btn-excluir, .btn-lida, .add-button { padding: 8px 10px; font-size: 13px; }
    }
  </style>
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
    <li><a href="calendario_diretor.php">📅Calendário</a></li>
    <li><a href="recursos_gen.php">🛠️ Gerenciar Recursos</a></li>
    <li><a href="editar_horarios.php">⏰ Editar Horários</a></li>
    <li><a href="bookings2.php">➕ Novo Agendamento</a></li>
    <li><a href="agendamentos_diretor.php">📝 Gerenciar Agendamentos</a></li>
    <li><a href="recursos_gen.php">💼 Recursos</a></li>
    <li><a href="cadastro_gestor1.php">👨‍🏫 Professor (a)</a></li>
    <li><a href="cadastro_gestor.php">👨‍🏫 Gestor (a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>

<div class="overlay" id="overlay"></div>

<div class="content">
  <h1>Mensagens dos Usuários </h1>

  <div class="mensagens-container">
    <?php if (empty($mensagens)): ?>
      <div class="vazio">Nenhuma mensagem recebida ainda.</div>
    <?php else: ?>
      <?php foreach ($mensagens as $msg): ?>
        <div class="card" style="<?= $msg['lida'] ? '' : 'border-left: 4px solid #2b6fb3;' ?>">
          <div class="info">
            <div><span>Nome</span><?= htmlspecialchars($msg['nome']); ?></div>
            <div><span>E-mail</span><?= htmlspecialchars($msg['email']); ?></div>
            <div><span>Telefone</span><?= htmlspecialchars($msg['telefone']); ?></div>
            <div><span>Instituição</span><?= htmlspecialchars($msg['instituicao']); ?></div>
            <div><span>Segmento</span><?= htmlspecialchars($msg['segmento']); ?></div>
          </div>

          <div class="mensagem"><?= nl2br(htmlspecialchars($msg['mensagem'])); ?></div>

          <div class="card-footer">
            <div class="data">📅 <?= date('d/m/Y H:i', strtotime($msg['data_envio'])); ?></div>
            <div>
              <?php if (!$msg['lida']): ?>
                <a href="mensagens.php?marcar_lida=<?= $msg['id']; ?>" class="btn-lida">Marcar como lida</a>
              <?php endif; ?>
              <a href="mensagens.php?excluir=<?= $msg['id']; ?>" class="btn-excluir" onclick="return confirm('Deseja realmente excluir esta mensagem?')">Excluir</a>
              <a class="add-button" href="pagina_diretor.php">Voltar</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Agenda Escolar. Todos os direitos reservados.
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
