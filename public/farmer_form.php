<?php
// Add a new farmer or edit one
require __DIR__ . '/../includes/init.php';
require_login();

$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

$errors = array();

// Starting values for the form
$first_name = '';
$last_name = '';
$contact_number = '';
$address = '';
$date_registered = date('Y-m-d');

// When editing, load the saved record
if ($id > 0) {
    $farmer = get_row('select * from farmer where farmer_id = ?', array($id));
    if (!$farmer) {
        set_message('warning', 'That farmer does not exist.');
        go_to('farmers.php');
    }
    $first_name = $farmer['first_name'];
    $last_name = $farmer['last_name'];
    $contact_number = $farmer['contact_number'];
    $address = $farmer['address'];
    $date_registered = $farmer['date_registered'];
}

// When the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $first_name      = posted('first_name');
    $last_name       = posted('last_name');
    $contact_number  = posted('contact_number');
    $address         = posted('address');
    $date_registered = posted('date_registered');

    // ----- Validation -----
    if ($first_name == '') {
        $errors['first_name'] = 'First name is required.';
    } elseif (strlen($first_name) > 50) {
        $errors['first_name'] = 'First name must be at most 50 characters.';
    }

    if ($last_name == '') {
        $errors['last_name'] = 'Last name is required.';
    } elseif (strlen($last_name) > 50) {
        $errors['last_name'] = 'Last name must be at most 50 characters.';
    }

    if ($contact_number == '') {
        $errors['contact_number'] = 'Contact number is required.';
    } elseif (!preg_match('/^[0-9+() -]{7,20}$/', $contact_number)) {
        $errors['contact_number'] = 'Contact number must be 7 to 20 digits (you may use + ( ) - and spaces).';
    }

    if ($address == '') {
        $errors['address'] = 'Address is required.';
    } elseif (strlen($address) > 150) {
        $errors['address'] = 'Address must be at most 150 characters.';
    }

    if ($date_registered == '') {
        $errors['date_registered'] = 'Date registered is required.';
    } elseif (!valid_date($date_registered)) {
        $errors['date_registered'] = 'Enter a valid date.';
    } elseif ($date_registered > date('Y-m-d')) {
        $errors['date_registered'] = 'Date registered cannot be in the future.';
    }

    // ----- Save if there are no errors -----
    if (count($errors) == 0) {
        try {
            if ($id > 0) {
                run_query('update farmer set first_name = ?, last_name = ?, contact_number = ?, address = ?,
                           date_registered = ?, updated_by = ? where farmer_id = ?',
                          array($first_name, $last_name, $contact_number, $address, $date_registered, current_user_id(), $id));
                set_message('success', 'Farmer updated successfully.');
            } else {
                $id = get_value('insert into farmer (first_name, last_name, contact_number, address, date_registered, created_by, updated_by)
                                 values (?, ?, ?, ?, ?, ?, ?) returning farmer_id',
                                array($first_name, $last_name, $contact_number, $address, $date_registered, current_user_id(), current_user_id()));
                set_message('success', 'Farmer created successfully.');
            }
            go_to('farmer_view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'A farmer with this contact number already exists.');
        }
    } else {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit Farmer' : 'Add Farmer';
$active = 'farmers';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="farmers.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="farmer_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php
    field_text('first_name', 'First name', $first_name, $errors, 50);
    field_text('last_name', 'Last name', $last_name, $errors, 50);
    field_text('contact_number', 'Contact number', $contact_number, $errors, 20, 'Digits, spaces, + ( ) and - only.');
    field_text('address', 'Address', $address, $errors, 150);
    field_date('date_registered', 'Date registered', $date_registered, $errors);
    ?>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="farmers.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
