<?php
// notify_admin.php - Interface de Configuração de Notificações
// Atualizado: Correção do retorno 'sent_message' para o alerta

$configFile = __DIR__ . '/../includes/notify_config.json';
$errors = [];
$success = false;

// Configuração padrão
$cfg = [
    "service" => "telegram",
    "telegram_chat_id" => "",
    "enabled" => false,
    "notify_temp_above" => null,
    "notify_hum_above" => null,
    "template" => "ALERTA: Temp {temp} °C, Hum {hum}% em {datahora}"
];

// Carregar config existente
if (file_exists($configFile)) {
    $raw = @file_get_contents($configFile);
    $parsed = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
        $cfg = array_merge($cfg, $parsed);
    }
}

// Inclui função de envio
require_once __DIR__ . '/../includes/notify_function.php';

// --- Handler AJAX: Teste de Envio ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) && $_POST['action'] === 'test')) {
    header('Content-Type: application/json; charset=utf-8');

    $template = $_POST['template'] ?? $cfg['template'];
    // Monta a mensagem de teste
    $message = str_replace(
        ['{temp}','{hum}','{datahora}','{id}'],
        [25.5, 55.2, date('Y-m-d H:i:s'), 'TEST'],
        $template
    );
    
    $chat_id = trim($_POST['telegram_chat_id'] ?? '');
    if (empty($chat_id)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensagem' => 'Chat ID é obrigatório para testar.']);
        exit;
    }

    // Envia
    $result = send_telegram_bot($chat_id, $message); 
    
    // RETORNA O JSON (Com o campo sent_message restaurado!)
    echo json_encode([ 
        'ok' => $result['ok'], 
        'http_code' => $result['http_code'], 
        'body' => $result['body'], 
        'error' => $result['error'], 
        'sent_message' => $message // <--- IMPORTANTE: Isso preenche o campo no alerta
    ]);
    exit;
}

// --- Handler: Salvar Configuração ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    
    $cfg['service'] = 'telegram';
    $cfg['enabled'] = isset($_POST['enabled']) && $_POST['enabled'] === '1';
    
    // Telegram
    $cfg['telegram_chat_id'] = trim($_POST['telegram_chat_id'] ?? '');
    
    // Configurações Comuns
    $cfg['template'] = trim($_POST['template'] ?? $cfg['template']);
    $nt = trim($_POST['notify_temp_above'] ?? '');
    $nh = trim($_POST['notify_hum_above'] ?? '');
    $cfg['notify_temp_above'] = $nt === '' ? null : floatval($nt);
    $cfg['notify_hum_above']  = $nh === '' ? null : floatval($nh);

    // Validação
    if ($cfg['enabled'] && (empty($cfg['telegram_chat_id']))) {
        $errors[] = "Para ativar, o Chat ID do Telegram é obrigatório.";
    }

    if (empty($errors)) {
        unset($cfg['phone']);
        unset($cfg['apikey']);
        unset($cfg['telegram_token']); 

        $saved = @file_put_contents($configFile, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($saved === false) { 
            $errors[] = "Erro ao gravar arquivo de configuração."; 
        } else { 
            $success = true; 
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Configurar Notificações</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
  <link href="../assets/css/admin.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
      body { background-color: var(--bg-light); padding-top: 2rem; }
      .config-container { max-width: 800px; margin: 0 auto; }
      .nav-tabs .nav-link { color: var(--primary-blue); font-weight: 500; }
      .nav-tabs .nav-link.active { color: var(--primary-blue); font-weight: 700; border-top: 3px solid var(--primary-blue); }
  </style>
</head>
<body>
  <div class="container config-container">
    
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="logo">N</div>
        <h3 class="m-0" style="color: var(--primary-blue); font-weight: 700;">Configurar Notificações</h3>
    </div>

    <?php if (!empty($errors)): ?> 
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div> 
    <?php endif; ?>

    <div class="card card-modern p-4">
        <form id="cfgForm" method="post">
            
            <ul class="nav nav-tabs mb-4" id="notifyTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="telegram-tab" data-bs-toggle="tab" data-bs-target="#telegram-content" type="button" role="tab"><i class="bi bi-telegram me-1"></i> Telegram</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp-content" type="button" role="tab"><i class="bi bi-whatsapp me-1"></i> WhatsApp</button>
                </li>
            </ul>

            <div class="tab-content" id="notifyTabsContent">
                
                <div class="tab-pane fade show active" id="telegram-content" role="tabpanel">
                    
                    <div class="form-check form-switch mb-4 ps-5">
                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" <?= $cfg['enabled'] ? 'checked' : '' ?> style="transform: scale(1.3); margin-left: -2.5em;">
                        <label class="form-check-label fw-bold ms-2 pt-1" for="enabled" style="color: var(--primary-blue);">
                            Ativar notificações via Telegram
                        </label>
                    </div>

                    <h5 class="mb-3" style="color: var(--primary-blue); border-left: 4px solid var(--ice-blue); padding-left: 10px;">Credenciais</h5>
                    <div class="mini-card mb-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Chat ID (Destino)</label>
                            <input id="telegram_chat_id" class="form-control" name="telegram_chat_id" value="<?= htmlspecialchars($cfg['telegram_chat_id']) ?>" placeholder="Ex: 123456789" />
                        </div>
                    </div>

                    <h5 class="mb-3" style="color: var(--primary-blue); border-left: 4px solid var(--ice-blue); padding-left: 10px;">Regras de Alerta</h5>
                    <div class="mini-card mb-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Template da mensagem</label>
                            <textarea id="template" class="form-control" name="template" rows="2"><?= htmlspecialchars($cfg['template']) ?></textarea>
                            <div class="form-text" style="font-size: 0.8rem;">Variáveis: <code>{temp}</code>, <code>{hum}</code>, <code>{datahora}</code></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Temp. acima de (°C)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-thermometer-high text-danger"></i></span>
                                    <input id="notify_temp_above" class="form-control" name="notify_temp_above" type="number" step="0.1" value="<?= $cfg['notify_temp_above'] === null ? '' : htmlspecialchars($cfg['notify_temp_above']) ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Umid. acima de (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-droplet-half text-primary"></i></span>
                                    <input id="notify_hum_above" class="form-control" name="notify_hum_above" type="number" step="0.1" value="<?= $cfg['notify_hum_above'] === null ? '' : htmlspecialchars($cfg['notify_hum_above']) ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button id="btnTest" class="btn btn-outline-primary" type="button"><i class="bi bi-send me-1"></i> Testar Telegram</button>
                        <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i> Salvar Configuração</button>
                    </div>
                </div>

                <div class="tab-pane fade" id="whatsapp-content" role="tabpanel">
                    <div class="text-center p-5" style="background-color: #f8f9fa; border-radius: 12px; border: 2px dashed #dee2e6;">
                        <i class="bi bi-whatsapp text-success mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">Integração WhatsApp</h4>
                        <p class="text-muted mb-0">Esta funcionalidade está em desenvolvimento e estará disponível em breve.</p>
                        <div class="mt-3 badge bg-warning text-dark">Em Breve</div>
                    </div>
                </div>

            </div>
        </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>

  <?php if ($success): ?>
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
              icon: 'success',
              title: 'Sucesso!',
              text: 'Configurações salvas com sucesso.',
              confirmButtonColor: '#113f80',
              timer: 3000,
              timerProgressBar: true
          });
      });
  </script>
  <?php endif; ?>

</body>
</html>