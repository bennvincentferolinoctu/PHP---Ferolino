<?php
// Add a user or edit one (user_form.php?id=2) - administrators only
require __DIR__ . '/../includes/init.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = array();

$role_options = get_rows('select role_id as id, role_name as label from user_role order by role_id');

$username = '';
$full_name = '';
$role_id = '2';        // new users are Staff by default
$is_active = true;
$password = '';

if ($id > 0) {
    $user = get_row('select user_id, username, full_name, role_id, is_active from app_user where user_id = ?', array($id));
    if (!$user) {
        set_message('warning', 'That user does not exist.');
        go_to('users.php');
    }
    $username = $user['username'];
    $full_name = $user['full_name'];
    $role_id = (string)$user['role_id'];
    $is_active = $user['is_active'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    if ($id == 0) {
        $username = posted('username');
    }
    $full_name = posted('full_name');
    $role_id   = posted('role_id');
    $password  = isset($_POST['password']) ? $_POST['password'] : '';
    $is_active = isset($_POST['is_active']);

    // ----- Validation -----
    if ($id == 0 && !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors['username'] = 'Username must be 3 to 50 letters, numbers, dot, dash or underscore.';
    }
    if ($full_name == '' || strlen($full_name) > 100) {
        $errors['full_name'] = 'Full name is required (maximum 100 characters).';
    }
    if (!in_options($role_options, $role_id)) {
        $errors['role_id'] = 'Please choose a role.';
    }
    if ($id == 0 && $password == '') {
        $errors['password'] = 'Password is required.';
    }
    if ($password != '' && (strlen($password) < 8 || strlen($password) > 72)) {
        $errors['password'] = 'Password must be 8 to 72 characters.';
    }
    // An administrator must not lock themselves out
    if ($id > 0 && $id == current_user_id() && (!$is_active || $role_id != '1')) {
        $errors['form'] = 'You cannot deactivate or demote your own account.';
    }

    // ----- Save -----
    if (count($errors) == 0) {
        try {
            $active_text = $is_active ? 'true' : 'false';
            if ($id == 0) {
                // password_hash() turns the password into a bcrypt hash - we never store the real password
                run_query('insert into app_user (username, full_name, password_hash, role_id, is_active) values (?, ?, ?, ?, ?)',
                          array($username, $full_name, password_hash($password, PASSWORD_DEFAULT), (int)$role_id, $active_text));
                set_message('success', 'User created successfully.');
            } else {
                run_query('update app_user set full_name = ?, role_id = ?, is_active = ? where user_id = ?',
                          array($full_name, (int)$role_id, $active_text, $id));
                if ($password != '') {
                    run_query('update app_user set password_hash = ? where user_id = ?', array(password_hash($password, PASSWORD_DEFAULT), $id));
                }
                set_message('success', 'User updated successfully.');
            }
            go_to('users.php');
        } catch (PDOException $e) {
            $errors['form'] = friendly_db_error($e, 'That username is already taken.');
        }
    } elseif (!isset($errors['form'])) {
        $errors['form'] = 'Please correct the highlighted fields.';
    }
}

$page_title = ($id > 0) ? 'Edit User' : 'Add User';
$active = 'users';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($page_title); ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="users.php">Back to list</a>
</div>
<?php if (isset($errors['form'])) { ?><div class="alert alert-danger"><?php echo e($errors['form']); ?></div><?php } ?>

<div class="card"><div class="card-body">
  <form method="post" novalidate class="row g-3" action="user_form.php<?php if ($id > 0) { echo '?id=' . $id; } ?>">
    <?php echo csrf_field(); ?>
    <?php if ($id == 0) {
        field_text('username', 'Username', $username, $errors, 50);
    } else { ?>
        <div class="col-md-6"><label class="form-label">Username</label>
          <input class="form-control" value="<?php echo e($username); ?>" readonly></div>
    <?php }
    field_text('full_name', 'Full name', $full_name, $errors, 100);
    field_select('role_id', 'Role', $role_options, $role_id, $errors);
    ?>
    <div class="col-md-6">
      <label class="form-label" for="password">Password <?php if ($id == 0) { echo '<span class="text-danger">*</span>'; } ?></label>
      <input type="password" class="form-control<?php echo isset($errors['password']) ? ' is-invalid' : ''; ?>" id="password" name="password" autocomplete="new-password" maxlength="72">
      <?php if (isset($errors['password'])) { ?><div class="invalid-feedback d-block"><?php echo e($errors['password']); ?></div>
      <?php } elseif ($id > 0) { ?><div class="form-text">Leave blank to keep the current password.</div><?php } ?>
    </div>
    <div class="col-12"><div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active"<?php if ($is_active) { echo ' checked'; } ?>>
      <label class="form-check-label" for="is_active">Account active</label>
    </div></div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Save</button>
      <a class="btn btn-light" href="users.php">Cancel</a>
    </div>
  </form>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
