<?php
// Add a new planting record or edit one
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = array();

$farm_options   = options_farm();
$crop_options   = options_crop();
$status_options = options_status();

$farm_id = '';
$crop_id = '';
$planting_date = '';
$area_planted = '';
$seed_quantity = '';
$expected_harvest_date = '';
$status_id = '';

if ($id > 0) {
    $planting = get_row('select * from planting where planting_id = ?', array($id));
    if (!$planting) {
        set_message('warning', 'That planting record does not exist.');
        go_to('plantings.php');
    }
    $farm_id = $planting['farm_id'];
    $crop_id = $planting['crop_id'];
    $planting_date = $planting['planting_date'];
    $area_planted = $planting['area_planted'];
    $seed_quantity = $planting['seed_quantity'];
    $expected_harvest_date = $planting['expected_harvest_date'];
    $status_id = $planting['status_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $farm_id               = posted('farm_id');
    $crop_id               = posted('crop_id');
    $planting_date         = posted('planting_date');
    $area_planted          = posted('area_planted');
    $seed_quantity         = posted('seed_quantity');
    $expected_harvest_date = posted('expected_harvest_date');
    $status_id             = posted('status_id');

    // ----- Validation -----
    if ($farm_id == '') {
        $errors['farm_id'] = 'Farm is required.';
    } elseif (!in_options($farm_options, $farm_id)) {
        $errors['farm_id'] = 'Please choose a farm from the list.';
    }

    if ($crop_id == '') {
        $errors['crop_id'] = 'Crop is required.';
    } elseif (!in_options($crop_options, $crop_id)) {
        $errors['crop_id'] = 'Please choose a crop from the list.';
    }

    if ($planting_date == '') {
        $errors['planting_date'] = 'Planting date is required.';
    } elseif (!valid_date($planting_date)) {
        $errors['planting_date'] = 'Enter a valid planting date.';
    }

    if ($area_planted == '') {
        $errors['area_planted'] = 'Area planted is required.';
    } elseif (!valid_decimal($area_planted)) {
        $errors['area_planted'] = 'Area planted must be a number with up to 2 decimals.';
    } elseif ($area_planted <= 0) {
        $errors['area_planted'] = 'Area planted must be greater than 0.';
    }

    if ($seed_quantity == '') {
        $errors['seed_quantity'] = 'Seed quantity is required.';
    } elseif (!valid_decimal($seed_quantity)) {
        $errors['seed_quantity'] = 'Seed quantity must be a positive number with up to 2 decimals.';
    } elseif ($seed_quantity <= 0) {
        $errors['seed_quantity'] = 'Seed quantity must be greater than 0.';
    }

    if ($expected_harvest_date == '') {
        $errors['expected_harvest_date'] = 'Expected harvest date is required.';
    } elseif (!valid_date($expected_harvest_date)) {
        $errors['expected_harvest_date'] = 'Enter a valid expected harvest date.';
    }

    if ($status_id == '') {
        $errors['status_id'] = 'Status is required.';
    } elseif (!in_options($status_options, $status_id)) {
        $errors['status_id'] = 'Please choose a status from the list.';
    }

    // Rule: the harvest must be expected AFTER planting
    if (!isset($errors['planting_date']) && !isset($errors['expected_harvest_date'])) {
        if ($expected_harvest_date <= $planting_date) {
            $errors['expected_harvest_date'] = 'Expected harvest date must be after the planting date.';
        }
    }

    // Rule: you cannot plant more area than the farm has
    if (!isset($errors['farm_id']) && !isset($errors['area_planted'])) {
        $farm_area = get_value('select area_hectares from farm where farm_id = ?', array((int)$farm_id));
        if ($farm_area !== null && $area_planted > $farm_area) {
            $errors['area_planted'] = 'Area planted cannot exceed the farm area (' . number_format($farm_area, 2) . ' ha).';
        }
    }

    // ----- Save -----
    if (count($errors) == 0) {
        try {
            if ($id > 0) {
                run_query('update planting set farm_id = ?, crop_id = ?, planting_date = ?, area_planted = ?, seed_quantity = ?,
                           expected_harvest_date = ?, status_id = ?, updated_by = ? where planting_id = ?',
                          array((int)$farm_id, (int)$crop_id, $planting_date, $area_planted, $seed_quantity, $expected_harvest_date, (int)$status_id, current_user_id(), $id));
                set_message('success', 'Planting record updated successfully.');
            } else {
                $id = get_value('insert into planting (farm_id, crop_id, planting_date, area_planted, seed_quantity, expected_harvest_date, status_id, created_by, updated_by)
                                 values (?, ?, ?, ?, ?, ?, ?, ?, ?) returning planting_id',
                                array((int)$farm_id, (int)$crop_id, $planting_date, $area_planted, $seed_quantity, $expected_harvest_date, (int)$status_id, current_user_id(), current_user_id()));
                set_message('success', 'Planting record created successfully.');
            }
            go_to('planting_view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'A record with the same value already exists.');
        }
    } else {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit Planting record' : 'Add Planting record';
$active = 'plantings';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="plantings.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="planting_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php
    field_select('farm_id', 'Farm', $farm_options, $farm_id, $errors);
    field_select('crop_id', 'Crop', $crop_options, $crop_id, $errors);
    field_date('planting_date', 'Planting date', $planting_date, $errors);
    field_number('area_planted', 'Area planted (ha)', $area_planted, $errors, 0.01, 100000, 0.01);
    field_number('seed_quantity', 'Seed quantity', $seed_quantity, $errors, 0.01, 999999, 0.01);
    field_date('expected_harvest_date', 'Expected harvest date', $expected_harvest_date, $errors);
    field_select('status_id', 'Status', $status_options, $status_id, $errors);
    ?>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="plantings.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
