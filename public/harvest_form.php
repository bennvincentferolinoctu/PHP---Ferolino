<?php
// Add a new harvest record or edit one
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = array();

$planting_options = options_planting();
$grade_options    = options_grade();
$remark_options   = options_remark();

$planting_id = '';
$harvest_date = '';
$quantity_kg = '';
$selling_price_per_kg = '';
$grade_id = '';
$remark_id = '';

if ($id > 0) {
    $harvest = get_row('select * from harvest where harvest_id = ?', array($id));
    if (!$harvest) {
        set_message('warning', 'That harvest record does not exist.');
        go_to('harvests.php');
    }
    $planting_id = $harvest['planting_id'];
    $harvest_date = $harvest['harvest_date'];
    $quantity_kg = $harvest['quantity_kg'];
    $selling_price_per_kg = $harvest['selling_price_per_kg'];
    $grade_id = $harvest['grade_id'];
    $remark_id = $harvest['remark_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $planting_id          = posted('planting_id');
    $harvest_date         = posted('harvest_date');
    $quantity_kg          = posted('quantity_kg');
    $selling_price_per_kg = posted('selling_price_per_kg');
    $grade_id             = posted('grade_id');
    $remark_id            = posted('remark_id');

    // ----- Validation -----
    if ($planting_id == '') {
        $errors['planting_id'] = 'Planting record is required.';
    } elseif (!in_options($planting_options, $planting_id)) {
        $errors['planting_id'] = 'Please choose a planting record from the list.';
    }

    if ($harvest_date == '') {
        $errors['harvest_date'] = 'Harvest date is required.';
    } elseif (!valid_date($harvest_date)) {
        $errors['harvest_date'] = 'Enter a valid harvest date.';
    }

    if ($quantity_kg == '') {
        $errors['quantity_kg'] = 'Quantity is required.';
    } elseif (!valid_decimal($quantity_kg)) {
        $errors['quantity_kg'] = 'Quantity must be a number with up to 2 decimals.';
    } elseif ($quantity_kg <= 0) {
        $errors['quantity_kg'] = 'Quantity must be greater than 0.';
    }

    if ($selling_price_per_kg == '') {
        $errors['selling_price_per_kg'] = 'Selling price is required.';
    } elseif (!valid_decimal($selling_price_per_kg)) {
        $errors['selling_price_per_kg'] = 'Selling price must be a number with up to 2 decimals.';
    }

    if ($grade_id == '') {
        $errors['grade_id'] = 'Quality grade is required.';
    } elseif (!in_options($grade_options, $grade_id)) {
        $errors['grade_id'] = 'Please choose a quality grade from the list.';
    }

    if ($remark_id == '') {
        $errors['remark_id'] = 'Remarks is required.';
    } elseif (!in_options($remark_options, $remark_id)) {
        $errors['remark_id'] = 'Please choose a remark from the list.';
    }

    // Rule: you cannot harvest before the crop was planted
    if (!isset($errors['planting_id']) && !isset($errors['harvest_date'])) {
        $planting_date = get_value('select planting_date from planting where planting_id = ?', array((int)$planting_id));
        if ($planting_date !== null && $harvest_date < $planting_date) {
            $errors['harvest_date'] = 'Harvest date cannot be earlier than the planting date (' . $planting_date . ').';
        }
    }

    // ----- Save -----
    if (count($errors) == 0) {
        try {
            if ($id > 0) {
                run_query('update harvest set planting_id = ?, harvest_date = ?, quantity_kg = ?, selling_price_per_kg = ?,
                           grade_id = ?, remark_id = ?, updated_by = ? where harvest_id = ?',
                          array((int)$planting_id, $harvest_date, $quantity_kg, $selling_price_per_kg, (int)$grade_id, (int)$remark_id, current_user_id(), $id));
                set_message('success', 'Harvest record updated successfully.');
            } else {
                $id = get_value('insert into harvest (planting_id, harvest_date, quantity_kg, selling_price_per_kg, grade_id, remark_id, created_by, updated_by)
                                 values (?, ?, ?, ?, ?, ?, ?, ?) returning harvest_id',
                                array((int)$planting_id, $harvest_date, $quantity_kg, $selling_price_per_kg, (int)$grade_id, (int)$remark_id, current_user_id(), current_user_id()));
                set_message('success', 'Harvest record created successfully.');
            }
            go_to('harvest_view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'A record with the same value already exists.');
        }
    } else {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit Harvest record' : 'Add Harvest record';
$active = 'harvests';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="harvests.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="harvest_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php
    field_select('planting_id', 'Planting record', $planting_options, $planting_id, $errors);
    field_date('harvest_date', 'Harvest date', $harvest_date, $errors);
    field_number('quantity_kg', 'Quantity (kg)', $quantity_kg, $errors, 0.01, 99999999, 0.01);
    field_number('selling_price_per_kg', 'Selling price per kg (PHP)', $selling_price_per_kg, $errors, 0, 99999999, 0.01);
    field_select('grade_id', 'Quality grade', $grade_options, $grade_id, $errors);
    field_select('remark_id', 'Remarks', $remark_options, $remark_id, $errors);
    ?>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="harvests.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
