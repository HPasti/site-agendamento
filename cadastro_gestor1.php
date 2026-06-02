<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();
requireDirector(); // CORREÇÃO #3: exige login de diretor

$message    = '';
$show_popup = false;
$email_mostrado = '';
$senha_gerada   = '';

function gerarSenhaAleatoria($tamanho = 8) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $senha;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim($_POST['email']);
    $nome   = trim($_POST['nome']);
    $escola = $_POST['escola'];

    if ($email && $nome && $escola) {
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $message = "Este e-mail já está cadastrado.";
        } else {
            $senha_aleatoria = gerarSenhaAleatoria();
            $senha_hash      = password_hash($senha_aleatoria, PASSWORD_DEFAULT);

            // CORREÇÃO #2: incluir role='professor' e primeiro_acesso=1
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, escola, role, primeiro_acesso)
                VALUES (?, ?, ?, ?, 'professor', 1)
            ");
            $stmt->execute([$nome, $email, $senha_hash, $escola]);

            $show_popup     = true;
            $email_mostrado = $email;
            $senha_gerada   = $senha_aleatoria;
        }
    } else {
        $message = "Preencha todos os campos.";
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cadastrar Professor(a) - Agenda Escolar</title>
<style>
:root{
  --blue:#2b6fb3;
  --blue-dark:#1a365d;
  --blue-light:#e8f3ff;
  --white:#ffffff;
  --red:#e74c3c;
  --radius:12px;
}
* { box-sizing:border-box; margin:0; padding:0; }
html, body { height:100%; font-family:"Inter",Arial,sans-serif; background:var(--white); color:var(--blue-dark); }
body { display:flex; flex-direction:column; min-height:100vh; }
header{ display:flex; justify-content:space-between; align-items:center; padding:18px 8%; background:var(--white); box-shadow:0 4px 12px rgba(0,0,0,0.05); position:sticky; top:0; z-index:100; }
.logo img { height:120px; }
.sidebar{ position:fixed; top:0; left:-260px; width:260px; height:100%; background:var(--blue-dark); color:var(--white); transition:left .3s ease; padding-top:80px; z-index:1000; }
.sidebar.active{left:0;}
.sidebar ul{list-style:none;padding:0;}
.sidebar ul li{padding:15px 24px;}
.sidebar ul li a{ color:var(--white); text-decoration:none; display:block; font-weight:500; transition:background .2s; }
.sidebar ul li a:hover{ background:rgba(255,255,255,0.15); border-radius:8px; }
.overlay{ position:fixed; top:0;left:0; width:100%;height:100%; background:rgba(0,0,0,0.4); display:none; z-index:999; }
.overlay.active{display:block;}
main { flex:1; display:flex; justify-content:center; align-items:center; padding:20px; }
.hero { display:flex; flex-wrap:wrap; gap:40px; max-width:900px; width:100%; background:var(--blue-light); border-radius:var(--radius); padding:40px; box-shadow:0 10px 30px rgba(20,40,80,0.08); animation:fadeIn 0.6s ease both; }
.hero-left { flex:1; display:flex; justify-content:center; align-items:center; }
.hero-left img { width:100%; max-width:450px; border-radius:var(--radius); }
.hero-right { flex:1; background:var(--white); padding:30px; border-radius:var(--radius); box-shadow:0 10px 25px rgba(20,40,80,0.06); display:flex; flex-direction:column; align-items:center; }
.hero-right h2 { margin-bottom:20px; color:var(--blue-dark); font-size:1.8rem; }
.hero-right form { width:100%; display:flex; flex-direction:column; gap:15px; }
.hero-right .input { width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; }
.hero-right select.input { appearance:none; -webkit-appearance:none; -moz-appearance:none; background:url('data:image/svg+xml;utf8,<svg fill="gray" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center; background-size:12px; }
.hero-right .btn-primary { width:100%; padding:12px; border:none; border-radius:10px; background:var(--blue-dark); color:var(--white); font-weight:600; cursor:pointer; transition:all 0.2s; }
.hero-right .btn-primary:hover { background:#1a2a4c; }
.error-msg { color:var(--red); font-weight:600; margin-bottom:12px; text-align:center; }
footer { background:var(--blue-dark); color:var(--white); text-align:center; padding:20px; font-size:0.9rem; margin-top:auto; }
@keyframes fadeUp{ from{opacity:0; transform:translateY(18px);} to{opacity:1; transform:translateY(0);} }
@keyframes fadeIn{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }
@media(max-width:900px){ .hero{flex-direction:column;} .hero-left img{max-width:350px; margin-bottom:20px;} }
</style>
</head>
<body>

<header>
  <div class="logo" id="logoBtn"><img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar"></div>
</header>

<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="pagina_diretor.php">🏠 Início</a></li>
    <li><a href="calendario_diretor.php">📅 Calendário</a></li>
    <li><a href="recursos_gen.php">🛠️ Gerenciar Recursos</a></li>
    <li><a href="editar_horarios.php">⏰ Editar Horários</a></li>
    <li><a href="bookings2.php">➕ Novo Agendamento</a></li>
    <li><a href="agendamentos_diretor.php">📝 Gerenciar Agendamentos</a></li>
    <li><a href="mensagens.php">✉️ Mensagens</a></li>
    <li><a href="cadastro_gestor.php">👨‍💼 Gestor(a)</a></li>
    <li><a href="perfil2.php">👤 Meu Perfil</a></li>
    <li><a href="logout.php">🚪 Sair</a></li>
  </ul>
</div>
<div class="overlay" id="overlay"></div>

<main>
<section class="hero">
  <div class="hero-left">
    <img src="assets/img.png" alt="Cadastro Professor">
  </div>
  <div class="hero-right">
    <h2>Cadastro de Professor(a)</h2>
    <?php if($message): ?>
      <p class="error-msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST">
      <input class="input" type="text"  name="nome"   placeholder="Nome completo" required>
      <input class="input" type="email" name="email"  placeholder="Email"         required>
      <select class="input" name="escola" required>
        <option value="">Selecione a escola</option>
        <option value="EEEFM Antonio dos Santos Neves">EEEFM Antônio dos Santos Neves</option>
      </select>
      <button class="btn-primary" type="submit">Cadastrar Professor(a)</button>
    </form>
  </div>
</section>
</main>

<footer>
  © <?php echo date('Y'); ?> Agenda Escolar - Todos os direitos reservados
</footer>

<?php if($show_popup): ?>
<div id="popup" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;justify-content:center;align-items:center;z-index:1000;">
  <div style="background:#fff;padding:30px;border-radius:12px;text-align:center;max-width:360px;box-shadow:0 8px 30px rgba(0,0,0,0.25);">
    <h2 style="margin-bottom:12px;color:#1a365d;">Professor(a) Cadastrado(a)!</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($email_mostrado) ?></p>
    <p><strong>Senha gerada:</strong> <?= htmlspecialchars($senha_gerada) ?></p>
    <p>Anote esta senha antes de prosseguir.</p>
    <button onclick="document.getElementById('popup').style.display='none'" style="margin-top:16px;background:#2b6fb3;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;">OK</button>
  </div>
</div>
<?php endif; ?>

<script>
const logoBtn = document.getElementById('logoBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
logoBtn.addEventListener('click', () => { sidebar.classList.toggle('active'); overlay.classList.toggle('active'); });
overlay.addEventListener('click', () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); });
</script>
</body>
</html>
