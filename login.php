<?php
session_start();
require_once 'auth.php';
$pdo = getPDO();

// ATENÇÃO: bloco de criação automática de administrador removido por segurança.
// Crie o usuário administrador manualmente no banco de dados antes do deploy:
// INSERT INTO usuarios (nome, email, senha, escola, role, primeiro_acesso)
// VALUES ('Nome Diretor', 'email@educador.edu.es.gov.br',
//         password_hash('SenhaForte!', PASSWORD_DEFAULT),
//         'EEEFM Antonio dos Santos Neves', 'diretor', 1);

$message = '';
$senha_alterar = false;
$usuario_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';

    // 🔥 RESTRIÇÃO DO DOMÍNIO (NOVO)
    if (!preg_match('/@educador\.edu\.es\.gov\.br$/', $email)) {
        $message = "Somente e-mails @educador.edu.es.gov.br são permitidos.";
    } else {

        // Buscar usuário pelo email
        $stmt = $pdo->prepare("SELECT id, nome, senha, escola, primeiro_acesso, role FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if (password_verify($senha, $usuario['senha'])) {

                if ($usuario['primeiro_acesso'] == 1) {
                    // Primeiro acesso, pede nova senha
                    if (!empty($nova_senha)) {
                        $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $up = $pdo->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 0 WHERE id = ?");
                        $up->execute([$nova_hash, $usuario['id']]);

                        // Loga o usuário
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['user_nome'] = $usuario['nome'];
                        $_SESSION['user_escola'] = $usuario['escola'];
                        $_SESSION['user_role'] = $usuario['role'];
                        // CORREÇÃO #4: redirecionar conforme perfil
                        $destino = ($usuario['role'] === 'diretor') ? 'pagina_diretor.php' : 'inicio.php';
                        header("Location: $destino");
                        exit;
                    }
                    $senha_alterar = true;
                    $usuario_id = $usuario['id'];
                } else {
                    // Login normal
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_nome'] = $usuario['nome'];
                    $_SESSION['user_escola'] = $usuario['escola'];
                    $_SESSION['user_role'] = $usuario['role'];
                    // CORREÇÃO #4: redirecionar conforme perfil
                    $destino = ($usuario['role'] === 'diretor') ? 'pagina_diretor.php' : 'inicio.php';
                    header("Location: $destino");
                    exit;
                }

            } else {
                $message = 'Senha incorreta.';
            }
        } else {
            $message = 'Email não encontrado.';
        }
    }
}

// Ajax para exibir escola automaticamente
if (isset($_GET['check_email'])) {
    $email_ajax = $_GET['check_email'];
    $stmt = $pdo->prepare("SELECT escola FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email_ajax]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $res ? htmlspecialchars($res['escola']) : '';
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Agenda Escolar</title>
<style>
:root{
  --blue-light:#e8f3ff;
  --blue:#2b6fb3;
  --blue-dark:#1a365d;
  --white:#ffffff;
  --red:#b00020;
  --radius:12px;
}

* { box-sizing: border-box; margin:0; padding:0; }
html, body { height:100%; overflow-x:hidden; }
body {
  display:flex;
  flex-direction:column;
  font-family: "Inter", Arial, sans-serif;
  background: var(--white);
  color: var(--blue-dark);
}

.header {
  display:flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 8%;
  background: var(--white);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: sticky;
  top: 0;
  z-index: 100;
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

main { flex:1; display:flex; justify-content:center; align-items:center; padding:20px; overflow:auto; }

.hero {
  display:flex;
  gap:40px;
  background: var(--blue-light);
  border-radius: var(--radius);
  box-shadow:0 10px 30px rgba(20,40,80,0.08);
  padding:40px;
  width:100%;
  max-width:900px;
  animation:fadeIn 0.6s ease both;
}
.hero-left { flex:1; display:flex; justify-content:center; align-items:center; }
.hero-left img { width:100%; max-width:400px; border-radius: var(--radius); }
.hero-right { flex:1; display:flex; flex-direction:column; }
.hero-right h2 { margin-bottom:16px; color: var(--blue-dark); }
.hero-right form { display:flex; flex-direction:column; gap:12px; }
.hero-right .input { padding:12px; border-radius:10px; border:1px solid #ccc; width:100%; }
.hero-right .btn-primary { padding:12px; background: var(--blue); color: var(--white); border:none; border-radius:10px; font-weight:600; cursor:pointer; transition: all 0.2s; }
.hero-right .btn-primary:hover { background:#1d4e7a; }
.error-msg { color: var(--red); text-align:center; font-weight:600; margin-bottom:12px; }

#escola-display { font-weight:600; color: var(--blue-dark); }

footer {
  background: var(--blue-dark);
  color: var(--white);
  text-align:center;
  padding:20px;
  font-size:0.9rem;
}

@media(max-width:900px) { 
  .hero { flex-direction:column; }
  .hero-left img { max-width:300px; margin-bottom:20px; }
}

@keyframes fadeIn{ 
  from{opacity:0; transform:translateY(8px);} 
  to{opacity:1; transform:translateY(0);} 
}
</style>
</head>
<body>

<header class="header">
  <div class="logo"><img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar"></div>
  <nav>
    <a href="index.php">Home</a>
  </nav>
</header>

<main>
  <section class="hero">
    <div class="hero-left">
      <img src="assets/calendario-removebg.png" alt="Imagem inicial">
    </div>
    <div class="hero-right">
      <h2>Login</h2>

      <?php if($message): ?>
        <p class="error-msg"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <form method="POST">
        <input class="input" type="email" name="email" id="email"
               placeholder="seu.nome@educador.edu.es.gov.br"
               required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <div id="escola-display"></div>

        <input class="input" type="password" name="senha" placeholder="Senha" required>
        
        <?php if($senha_alterar): ?>
          <input class="input" type="password" name="nova_senha" placeholder="Digite uma nova senha" required>
          <button class="btn-primary" type="submit">Definir Nova Senha</button>
        <?php else: ?>
          <button class="btn-primary" type="submit">Entrar</button>
        <?php endif; ?>
      </form>
    </div>
  </section>
</main>

<footer>
  &copy; <?= date('Y'); ?> Agenda Escolar. Todos os direitos reservados.
</footer>

<script>
// Exibir escola automaticamente ao digitar email
document.getElementById('email').addEventListener('input', function() {
    let email = this.value;
    if(email.length < 3){ 
        document.getElementById('escola-display').textContent = '';
        return;
    }
    fetch(`login.php?check_email=${encodeURIComponent(email)}`)
    .then(res => res.text())
    .then(data => {
        document.getElementById('escola-display').textContent = data ? "Escola: " + data : "";
    });
});
</script>

</body>
</html>
