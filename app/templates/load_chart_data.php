<?php
require_once '../../config/constants.php';
require_once CLASSES_PATH . 'database_handler.php';
$db = new DatabaseHandler(DB_PATH);
$table = $_GET['table'] ?? '';
$dateRange = $_GET['dateRange'] ?? '7';
$granularity = $_GET['granularity'] ?? 'day';
$chartData = $db->getChartData($table, $dateRange, $granularity);
echo json_encode([
    'chartData' => json_encode($chartData)
]);
