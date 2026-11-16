<?php
require __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    go_to('plantings.php');
}
check_csrf();
if (!is_admin()) {
    set_message('danger', 'Only administrators can delete records.');
    go_to('plantings.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$planting = get_row('select * from planting where planting_id = ?', array($id));
if (!$planting) {
    set_message('warning', 'That planting record no longer exists.');
    go_to('plantings.php');
}

$harvest_count = get_value('select count(*) from harvest where planting_id = ?', array($id));
if ($harvest_count > 0) {
    set_message('danger', 'Cannot delete planting #' . $id . ': it still has ' . $harvest_count .
                ' harvest record(s). Remove them first.');
    go_to('plantings.php');
}

try {
    run_query('delete from planting where planting_id = ?', array($id));
    set_message('success', 'Planting record deleted successfully.');
} catch (PDOException $e) {
    set_message('danger', friendly_db_error($e, 'Could not delete this record.'));
}
go_to('plantings.php');
