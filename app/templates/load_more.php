<?php
require_once '../../config/constants.php';
require_once CLASSES_PATH . 'database_handler.php';
$db = new DatabaseHandler(DB_PATH);
$offset = $_GET['offset'] ?? 0;
$table = $_GET['table'] ?? '';
$tableData = $db->getTableData($table, 5, $offset); // Modify the limit value as per your requirement
$moreData = $db->hasMoreData($table, $offset + 5); // Modify the limit value as per your requirement
$rowsHTML = '';
foreach ($tableData as $row) {
    $rowsHTML .= '<tr>';
    foreach ($row as $value) {
        $rowsHTML .= "<td>$value</td>";
    }
    $rowsHTML .= '</tr>';
}
// at the end of load_more.php
echo json_encode([
    'rowsHTML' => $rowsHTML,
    'moreData' => $moreData
]);
