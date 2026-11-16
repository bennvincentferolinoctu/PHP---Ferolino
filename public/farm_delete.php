<?php
require __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    go_to('farms.php');
}
check_csrf();
if (!is_admin()) {
    set_message('danger', 'Only administrators can delete records.');
    go_to('farms.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$farm = get_row('select * from farm where farm_id = ?', array($id));
if (!$farm) {
    set_message('warning', 'That farm no longer exists.');
    go_to('farms.php');
}

// A farm that still has planting records cannot be deleted
$planting_count = get_value('select count(*) from planting where farm_id = ?', array($id));
if ($planting_count > 0) {
    set_message('danger', 'Cannot delete ' . $farm['farm_name'] . ': it is still used by ' . $planting_count .
                ' planting record(s). Remove or reassign them first.');
    go_to('farms.php');
}

try {
    run_query('delete from farm where farm_id = ?', array($id));
    set_message('success', 'Farm deleted successfully.');
} catch (PDOException $e) {
    set_message('danger', friendly_db_error($e, 'Could not delete this farm.'));
}
go_to('farms.php');
