<?php
// salvar_dht.php - VERSÃO CORRIGIDA (TIMEZONE)
// Apenas salva os dados no banco de dados (tabela dht) para o histórico.

// IMPORTANTE: Traz o Timezone correto (Brasília)
require_once __DIR__ . '/../includes/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = "localhost";
$db   = "esp_monitor";
$user = "root";
$pass = "";

// conexão com tratamento
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    error_log("salvar_dht.php: Erro de conexão com o DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Falha na conexão DB: " . $e->getMessage()]);
    exit;
}

// obter parametros (aceita GET ou POST)
$temp_raw = $_GET['temp'] ?? $_POST['temp'] ?? null;
$hum_raw  = $_GET['hum']  ?? $_POST['hum']  ?? null;

if ($temp_raw === null || $hum_raw === null) {
    error_log("salvar_dht.php: Erro: 'temp' ou 'hum' não fornecidos");
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "Parâmetros 'temp' e 'hum' obrigatórios"]);
    $conn->close();
    exit;
}

if (!is_numeric($temp_raw) || !is_numeric($hum_raw)) {
    error_log("salvar_dht.php: Erro: Parâmetros inválidos: temp={$temp_raw}, hum={$hum_raw}");
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "Parâmetros inválidos: temp={$temp_raw}, hum={$hum_raw}"]);
    $conn->close();
    exit;
}

$temp = floatval($temp_raw);
$hum  = floatval($hum_raw);

// AGORA PEGA A HORA CERTA (Brasília) GRAÇAS AO config.php
$datahora = date('Y-m-d H:i:s');

try {
    // Ação única: Inserir no histórico
    $stmt = $conn->prepare("INSERT INTO dht (temperatura, umidade, datahora) VALUES (?, ?, ?)");
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("dds", $temp, $hum, $datahora);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $insertedId = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        "status" => "ok", 
        "mensagem" => "Salvo no histórico", 
        "id" => $insertedId,
        "hora_salva" => $datahora
    ]);
    $conn->close();
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    if (isset($stmt) && $stmt) $stmt->close();
    $conn->close();
    exit;
}
?>