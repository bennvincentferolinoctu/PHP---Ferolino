<?php
require __DIR__ . '/../includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    go_to('crops.php');
}
check_csrf();
if (!is_admin()) {
    set_message('danger', 'Only administrators can delete records.');
    go_to('crops.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$crop = get_row('select * from crop where crop_id = ?', array($id));
if (!$crop) {
    set_message('warning', 'That crop no longer exists.');
    go_to('crops.php');
}

$planting_count = get_value('select count(*) from planting where crop_id = ?', array($id));
if ($planting_count > 0) {
    set_message('danger', 'Cannot delete ' . $crop['crop_name'] . ': it is still used by ' . $planting_count .
                ' planting record(s). Remove or reassign them first.');
    go_to('crops.php');
}

try {
    run_query('delete from crop where crop_id = ?', array($id));
    set_message('success', 'Crop deleted successfully.');
} catch (PDOException $e) {
    set_message('danger', friendly_db_error($e, 'Could not delete this crop.'));
}
go_to('crops.php');
