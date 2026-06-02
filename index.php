<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda Escolar</title>
  <style>
    :root {
      --blue-dark: #1a365d;
      --blue: #2b6fb3;
      --blue-light: #e8f3ff;
      --white: #ffffff;
      --radius: 12px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: "Inter", Arial, sans-serif;
      color: var(--blue-dark);
      background: var(--white);
      line-height: 1.5;
    }

    /* Header */
    header {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 80px; /* espaço entre logo, nav e botão */
      padding: 18px 8%;
      background: var(--white);
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .logo img { 
      height: 120px; /* tamanho aumentado */
    }

    nav {
      display: flex;
      gap: 40px; /* espaço entre os links */
      margin-left: auto; /* empurra o nav pra direita */
      margin-right: 80px; /* distância do botão "Acessar Plataforma" */
    }

    nav a {
      text-decoration: none;
      font-weight: 600;
      color: var(--blue-dark);
      transition: color 0.2s;
    }

    nav a:hover { color: var(--blue); }

    /* Aba de Conta (botão azul) */
    .account-tab {
      position: relative;
      cursor: pointer;
      user-select: none;
    }

    .account-tab span {
      background-color: var(--blue);
      color: var(--white);
      padding: 10px 15px;
      border-radius: var(--radius);
      font-weight: 600;
      transition: background 0.2s;
    }

    .account-tab span:hover {
      background-color: #1e549a;
    }

    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 45px;
      background-color: var(--white);
      border-radius: var(--radius);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      z-index: 100;
    }

    .dropdown a {
      display: block;
      padding: 12px 20px;
      color: var(--blue-dark);
      text-decoration: none;
      transition: background 0.2s;
    }

    .dropdown a:hover {
      background-color: var(--blue-light);
    }

    /* Hero */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 60px 8%;
      background: var(--blue-light);
      flex-wrap: wrap;
      gap: 40px;
    }
    .hero-text {
      flex: 1;
      max-width: 500px;
    }
    .hero-text h1 {
      font-size: 2.5rem;
      margin-bottom: 16px;
      color: var(--blue-dark);
    }
    .hero-text p {
      margin-bottom: 24px;
      font-size: 1.1rem;
      color: #23445f;
    }

    /* Botão de Agendamento */
    #agendarTab {
      display: inline-block;
      position: relative;
      cursor: pointer;
      margin-top: 20px;
    }
    #agendarTab span {
      background-color: var(--blue);
      color: var(--white);
      padding: 14px 28px;
      border-radius: var(--radius);
      font-weight: 600;
      transition: background 0.2s;
      display: inline-block;
    }
    #agendarTab span:hover {
      background-color: #1e549a;
    }
    #dropdownAgendar {
      display: none;
      position: absolute;
      top: 50px;
      left: 0;
      background-color: var(--white);
      border-radius: var(--radius);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      z-index: 100;
    }
    #dropdownAgendar a {
      display: block;
      padding: 12px 20px;
      color: var(--blue-dark);
      text-decoration: none;
      transition: background 0.2s;
    }
    #dropdownAgendar a:hover {
      background-color: var(--blue-light);
    }

    .hero img { max-width: 420px; width: 100%; }

    /* Cards */
    section {
      padding: 60px 8%;
    }
    .section-title {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 32px;
      color: var(--blue-dark);
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
      gap: 20px;
    }
    .card {
      background: var(--white);
      padding: 24px;
      border-radius: var(--radius);
      box-shadow: 0 4px 16px rgba(0,0,0,0.2);
      text-align: center;
      transition: transform 0.2s;
    }
    .card:hover { transform: translateY(-4px); }
    .card h3 { margin-bottom: 10px; color: var(--blue-dark); }

    /* CTA */
    .cta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 40px;
      background: var(--blue-light);
      border-radius: var(--radius);
      padding: 40px;
      margin: 40px 8%;
      flex-wrap: wrap;
    }
    .cta-text { flex: 1; }
    .cta-text h2 { font-size: 1.8rem; margin-bottom: 16px; }
    .cta img { max-width: 320px; border-radius: var(--radius); }

    /* Footer */
    footer {
      background: var(--blue-dark);
      color: var(--white);
      text-align: center;
      padding: 20px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="imagens/AgendaEscolar-removebg.png" alt="Agenda Escolar">
    </div>
    
    <nav>
      <a href="sobre.php">Sobre</a>
      <a href="contato.php">Contato</a>
    </nav>

    <div class="account-tab" id="accountTab">
      <span>Acessar Plataforma ▼</span>
      <div class="dropdown" id="dropdownMenu">
        <a href="login.php">Professor(a)</a>
       <a href="login1.php">Gestores(as)</a>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-text">
      <h1>Agende os recursos da escola de forma rápida e organizada</h1>
      <p>Chromebooks, Laboratório, Óculos VR, Canetas 3D e muito mais.</p>

      <div class="account-tab" id="agendarTab">
        <span>Acessar Plataforma</span>
        <div class="dropdown" id="dropdownAgendar">
          <a href="login.php">Professor(a)</a>
          <a href="login1.php">Gestores(as)</a>
        </div>
      </div>
    </div>

    <img src="assets/calendario-removebg.png" alt="Calendário">
  </section>

  <!-- Cards -->
  <section>
    <h2 class="section-title">Por que usar a Agenda Escolar?</h2>
    <div class="cards">
      <div class="card"><h3>Maior organização</h3><p>Organização total na sua escola.</p></div>
      <div class="card"><h3>Agilidade</h3><p>Agendamentos rápidos e sem conflitos.</p></div>
      <div class="card"><h3>Transparência</h3><p>Relatórios para gestores.</p></div>
      <div class="card"><h3>Adaptável</h3><p>Acesse de qualquer dispositivo.</p></div>
    </div>
  </section>
        
  <h2 class="section-title">Como usar a Agenda Escolar?</h2>
  <section class="cards">
      <div class="card">
        <h3>📚 Escolha o recurso</h3>
        <p class="small">Selecione o recurso que você quer reservar.</p>
      </div>
      <div class="card">
        <h3>⏰ Selecione a data e horário</h3>
        <p class="small">Veja a disponibilidade no calendário.</p>
      </div>
      <div class="card">
        <h3>✅ Confirme sua reserva</h3>
        <p class="small">Receba confirmação imediata.</p>
      </div>
      <div class="card">
        <h3>🎉 Use sem preocupações</h3>
        <p class="small">Utilize o recurso no horário reservado.</p>
      </div>
    </section>

  <!-- CTA -->
  <section class="cta">
    <div class="cta-text">
      <h2>"Organize sua escola com a gente"</h2>
      <p>Agende recursos de forma prática, rápida e organizada.</p>
    </div>
    <img src="assets/mulher.png" alt="Escola">
  </section>

  <!-- Footer -->
  <footer>
    <p>© <?php echo date('Y'); ?> Agenda Escolar - Todos os direitos reservados</p>
  </footer>

  <script>
    // Dropdown da aba de Conta
    const accountTab = document.getElementById('accountTab');
    const dropdownMenu = document.getElementById('dropdownMenu');

    accountTab.addEventListener('click', () => {
      dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // Dropdown do botão Agendar
    const agendarTab = document.getElementById('agendarTab');
    const dropdownAgendar = document.getElementById('dropdownAgendar');

    agendarTab.addEventListener('click', () => {
      dropdownAgendar.style.display = dropdownAgendar.style.display === 'block' ? 'none' : 'block';
    });

    // Fecha dropdowns se clicar fora
    window.addEventListener('click', function(e) {
      if (!accountTab.contains(e.target)) dropdownMenu.style.display = 'none';
      if (!agendarTab.contains(e.target)) dropdownAgendar.style.display = 'none';
    });
  </script>

</body>
</html>
