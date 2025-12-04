<?php
// get_ultimo.php - versão robusta para depuração e CORS
// Inclui config para garantir consistência
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'esp_monitor';

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    http_response_code(500);
    error_log("get_ultimo.php - DB connect error: ".$conn->connect_error);
    echo json_encode(['status'=>'erro','mensagem'=>'Falha conexao DB']);
    exit;
}

if (!$res = $conn->query("SELECT temperatura, umidade, datahora FROM dht ORDER BY id DESC LIMIT 1")) {
    http_response_code(500);
    error_log("get_ultimo.php - Query error: ".$conn->error);
    echo json_encode(['status'=>'erro','mensagem'=>'Erro na query']);
    $conn->close();
    exit;
}

$row = $res->fetch_assoc();
if (!$row) {
    echo json_encode(['status'=>'ok','mensagem'=>'sem dados']);
} else {
    // Retorna os dados (a datahora já virá correta do banco)
    echo json_encode([
        'status' => 'ok',
        'temperatura' => floatval($row['temperatura']),
        'umidade' => floatval($row['umidade']),
        'datahora' => $row['datahora']
    ]);
}
$conn->close();
?>