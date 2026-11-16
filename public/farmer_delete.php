<?php
require __DIR__ . '/../includes/init.php';
require_login();

// Deleting only works from the Delete button (POST) and only for administrators
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    go_to('farmers.php');
}
check_csrf();
if (!is_admin()) {
    set_message('danger', 'Only administrators can delete records.');
    go_to('farmers.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$farmer = get_row('select * from farmer where farmer_id = ?', array($id));
if (!$farmer) {
    set_message('warning', 'That farmer no longer exists.');
    go_to('farmers.php');
}

// A farmer who still owns farms cannot be deleted
$farm_count = get_value('select count(*) from farm where farmer_id = ?', array($id));
if ($farm_count > 0) {
    set_message('danger', 'Cannot delete ' . $farmer['first_name'] . ' ' . $farmer['last_name'] .
                ': this farmer still has ' . $farm_count . ' farm(s). Remove or reassign them first.');
    go_to('farmers.php');
}

try {
    run_query('delete from farmer where farmer_id = ?', array($id));
    set_message('success', 'Farmer deleted successfully.');
} catch (PDOException $e) {
    set_message('danger', friendly_db_error($e, 'Could not delete this farmer.'));
}
go_to('farmers.php');
