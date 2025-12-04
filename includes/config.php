<?php
// includes/config.php
// ARQUIVO CENTRAL DE CONFIGURAÇÃO

// 1. DEFINE O FUSO HORÁRIO PARA BRASIL/SÃO PAULO
// Isso garante que date('Y-m-d H:i:s') pegue a hora certa.
date_default_timezone_set('America/Sao_Paulo');

// Token do Telegram (usado pelo notify_function.php)
define('TELEGRAM_BOT_TOKEN', '8296168103:AAEr5GGv1ieNOQTU0_3xI0rVxNznI8HPdkc');
?>