<?php
// Add a new farm or edit one
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = array();

// Dropdown choices
$farmer_options     = options_farmer();
$soil_options       = options_soil();
$irrigation_options = options_irrigation();

// Starting values
$farmer_id = '';
$farm_name = '';
$location = '';
$area_hectares = '';
$soil_type_id = '';
$irrigation_type_id = '';

if ($id > 0) {
    $farm = get_row('select * from farm where farm_id = ?', array($id));
    if (!$farm) {
        set_message('warning', 'That farm does not exist.');
        go_to('farms.php');
    }
    $farmer_id = $farm['farmer_id'];
    $farm_name = $farm['farm_name'];
    $location = $farm['location'];
    $area_hectares = $farm['area_hectares'];
    $soil_type_id = $farm['soil_type_id'];
    $irrigation_type_id = $farm['irrigation_type_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $farmer_id          = posted('farmer_id');
    $farm_name          = posted('farm_name');
    $location           = posted('location');
    $area_hectares      = posted('area_hectares');
    $soil_type_id       = posted('soil_type_id');
    $irrigation_type_id = posted('irrigation_type_id');

    // ----- Validation -----
    if ($farmer_id == '') {
        $errors['farmer_id'] = 'Farmer is required.';
    } elseif (!in_options($farmer_options, $farmer_id)) {
        $errors['farmer_id'] = 'Please choose a farmer from the list.';
    }

    if ($farm_name == '') {
        $errors['farm_name'] = 'Farm name is required.';
    } elseif (strlen($farm_name) > 100) {
        $errors['farm_name'] = 'Farm name must be at most 100 characters.';
    }

    if ($location == '') {
        $errors['location'] = 'Location is required.';
    } elseif (strlen($location) > 150) {
        $errors['location'] = 'Location must be at most 150 characters.';
    }

    if ($area_hectares == '') {
        $errors['area_hectares'] = 'Area is required.';
    } elseif (!valid_decimal($area_hectares)) {
        $errors['area_hectares'] = 'Area must be a number with up to 2 decimals.';
    } elseif ($area_hectares < 0.01 || $area_hectares > 100000) {
        $errors['area_hectares'] = 'Area must be between 0.01 and 100000 hectares.';
    }

    if ($soil_type_id == '') {
        $errors['soil_type_id'] = 'Soil type is required.';
    } elseif (!in_options($soil_options, $soil_type_id)) {
        $errors['soil_type_id'] = 'Please choose a soil type from the list.';
    }

    if ($irrigation_type_id == '') {
        $errors['irrigation_type_id'] = 'Irrigation type is required.';
    } elseif (!in_options($irrigation_options, $irrigation_type_id)) {
        $errors['irrigation_type_id'] = 'Please choose an irrigation type from the list.';
    }

    // ----- Save -----
    if (count($errors) == 0) {
        try {
            if ($id > 0) {
                run_query('update farm set farmer_id = ?, farm_name = ?, location = ?, area_hectares = ?,
                           soil_type_id = ?, irrigation_type_id = ?, updated_by = ? where farm_id = ?',
                          array((int)$farmer_id, $farm_name, $location, $area_hectares, (int)$soil_type_id, (int)$irrigation_type_id, current_user_id(), $id));
                set_message('success', 'Farm updated successfully.');
            } else {
                $id = get_value('insert into farm (farmer_id, farm_name, location, area_hectares, soil_type_id, irrigation_type_id, created_by, updated_by)
                                 values (?, ?, ?, ?, ?, ?, ?, ?) returning farm_id',
                                array((int)$farmer_id, $farm_name, $location, $area_hectares, (int)$soil_type_id, (int)$irrigation_type_id, current_user_id(), current_user_id()));
                set_message('success', 'Farm created successfully.');
            }
            go_to('farm_view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'This farmer already has a farm with that name.');
        }
    } else {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit Farm' : 'Add Farm';
$active = 'farms';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="farms.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="farm_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php
    field_select('farmer_id', 'Farmer', $farmer_options, $farmer_id, $errors);
    field_text('farm_name', 'Farm name', $farm_name, $errors, 100);
    field_text('location', 'Location', $location, $errors, 150);
    field_number('area_hectares', 'Area (hectares)', $area_hectares, $errors, 0.01, 100000, 0.01);
    field_select('soil_type_id', 'Soil type', $soil_options, $soil_type_id, $errors);
    field_select('irrigation_type_id', 'Irrigation type', $irrigation_options, $irrigation_type_id, $errors);
    ?>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="farms.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
