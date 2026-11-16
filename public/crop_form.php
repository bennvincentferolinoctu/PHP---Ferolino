<?php
// Add a new crop (no id) or edit one (crop_form.php?id=2)
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = array();

$category_options = options_category();
$season_options   = options_season();

$crop_name = '';
$variety = '';
$crop_category_id = '';
$growing_period_days = '';
$season_id = '';

if ($id > 0) {
    $crop = get_row('select * from crop where crop_id = ?', array($id));
    if (!$crop) {
        set_message('warning', 'That crop does not exist.');
        go_to('crops.php');
    }
    $crop_name = $crop['crop_name'];
    $variety = $crop['variety'];
    $crop_category_id = $crop['crop_category_id'];
    $growing_period_days = $crop['growing_period_days'];
    $season_id = $crop['season_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $crop_name           = posted('crop_name');
    $variety             = posted('variety');
    $crop_category_id    = posted('crop_category_id');
    $growing_period_days = posted('growing_period_days');
    $season_id           = posted('season_id');

    if ($crop_name == '') {
        $errors['crop_name'] = 'Crop name is required.';
    } elseif (strlen($crop_name) > 80) {
        $errors['crop_name'] = 'Crop name must be at most 80 characters.';
    }

    if ($variety == '') {
        $errors['variety'] = 'Variety is required.';
    } elseif (strlen($variety) > 80) {
        $errors['variety'] = 'Variety must be at most 80 characters.';
    }

    if ($crop_category_id == '') {
        $errors['crop_category_id'] = 'Category is required.';
    } elseif (!in_options($category_options, $crop_category_id)) {
        $errors['crop_category_id'] = 'Please choose a category from the list.';
    }

    if ($growing_period_days == '') {
        $errors['growing_period_days'] = 'Growing period is required.';
    } elseif (!valid_whole_number($growing_period_days)) {
        $errors['growing_period_days'] = 'Growing period must be a whole number of days.';
    } elseif ($growing_period_days < 1 || $growing_period_days > 1000) {
        $errors['growing_period_days'] = 'Growing period must be between 1 and 1000 days.';
    }

    if ($season_id == '') {
        $errors['season_id'] = 'Season is required.';
    } elseif (!in_options($season_options, $season_id)) {
        $errors['season_id'] = 'Please choose a season from the list.';
    }

    if (count($errors) == 0) {
        try {
            if ($id > 0) {
                run_query('update crop set crop_name = ?, variety = ?, crop_category_id = ?, growing_period_days = ?,
                           season_id = ?, updated_by = ? where crop_id = ?',
                          array($crop_name, $variety, (int)$crop_category_id, (int)$growing_period_days, (int)$season_id, current_user_id(), $id));
                set_message('success', 'Crop updated successfully.');
            } else {
                $id = get_value('insert into crop (crop_name, variety, crop_category_id, growing_period_days, season_id, created_by, updated_by)
                                 values (?, ?, ?, ?, ?, ?, ?) returning crop_id',
                                array($crop_name, $variety, (int)$crop_category_id, (int)$growing_period_days, (int)$season_id, current_user_id(), current_user_id()));
                set_message('success', 'Crop created successfully.');
            }
            go_to('crop_view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'This crop and variety combination already exists.');
        }
    } else {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit Crop' : 'Add Crop';
$active = 'crops';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="crops.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="crop_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php
    field_text('crop_name', 'Crop name', $crop_name, $errors, 80);
    field_text('variety', 'Variety', $variety, $errors, 80);
    field_select('crop_category_id', 'Category', $category_options, $crop_category_id, $errors);
    field_number('growing_period_days', 'Growing period (days)', $growing_period_days, $errors, 1, 1000, 1);
    field_select('season_id', 'Recommended season', $season_options, $season_id, $errors);
    ?>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="crops.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
