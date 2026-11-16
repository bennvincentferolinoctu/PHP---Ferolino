<?php
require __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    go_to('harvests.php');
}
check_csrf();
if (!is_admin()) {
    set_message('danger', 'Only administrators can delete records.');
    go_to('harvests.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$harvest = get_row('select * from harvest where harvest_id = ?', array($id));
if (!$harvest) {
    set_message('warning', 'That harvest record no longer exists.');
    go_to('harvests.php');
}

try {
    run_query('delete from harvest where harvest_id = ?', array($id));
    set_message('success', 'Harvest record deleted successfully.');
} catch (PDOException $e) {
    set_message('danger', friendly_db_error($e, 'Could not delete this record.'));
}
go_to('harvests.php');
