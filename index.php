<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ESP Monitor - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/menu.css" rel="stylesheet">
</head>
<body>

  <div class="sidebar d-flex flex-column">
    <div class="logo d-flex align-items-center mb-3">
      <i class="bi bi-speedometer2 me-2"></i>
      <span>M.A.S</span> </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="pagina_dashboard.html" class="nav-link active" target="contentFrame">
          <i class="bi bi-graph-up"></i>
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="pagina_historico.html" class="nav-link" target="contentFrame">
          <i class="bi bi-clock-history"></i>
          Histórico (BD)
        </a>
      </li>
      <li class="nav-item">
        <a href="api/notify_admin.php" class="nav-link" target="contentFrame">
          <i class="bi bi-telegram"></i> Notificações
        </a>
      </li>
    </ul>
    <hr>
    <div class="small text-muted">Monitoramento v1.0.1</div>
  </div>

  <iframe name="contentFrame" src="pagina_dashboard.html" class="content-frame">
  </iframe>

  <script>
    // Script para marcar o link ativo no menu
    document.addEventListener('DOMContentLoaded', function() {
      const links = document.querySelectorAll('.sidebar .nav-link');
      
      links.forEach(link => {
        link.addEventListener('click', function() {
          links.forEach(l => l.classList.remove('active'));
          this.classList.add('active');
        });
      });
    });
  </script>
</body>
</html>