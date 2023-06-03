<?php
require_once 'config/constants.php';
require_once CLASSES_PATH . 'database_handler.php';
$db = new DatabaseHandler(DB_PATH);
$tableNames = $db->getTableNames();
include ROOT_PATH . '/app/templates/header.php';
?>
<main class="main content fp_flex fp_flex-col">
    <?php foreach ($tableNames as $tableName) : ?>
        <?php
        $tableData = $db->getTableData($tableName, 5, 0); // Modify the limit and offset values as per your requirement
        include ROOT_PATH . '/app/templates/accordion.php';
        ?>
    <?php endforeach; ?>
</main>
<?php include ROOT_PATH . '/app/templates/footer.php'; ?>
