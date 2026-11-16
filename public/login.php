<?php
require __DIR__ . '/../includes/init.php';

// Already logged in? Go to the dashboard.
if (isset($_SESSION['user'])) {
    go_to('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();

    $username = posted('username');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!isset($_SESSION['fails'])) { $_SESSION['fails'] = 0; }

    if (isset($_SESSION['locked_until']) && $_SESSION['locked_until'] > time()) {
        $error = 'Too many failed attempts. Please wait one minute.';
    } elseif ($username == '' || $password == '') {
        $error = 'Enter your username and password.';
    } else {
        // Find the user
        $user = get_row('select u.user_id, u.username, u.full_name, u.password_hash, u.role_id, u.is_active, r.role_name
                         from app_user u
                         join user_role r on r.role_id = u.role_id
                         where lower(u.username) = lower(?)', array($username));

        // password_verify() compares the typed password with the saved bcrypt hash
        if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);   // new session id after login
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            $_SESSION['fails'] = 0;
            set_message('success', 'Welcome, ' . $user['full_name'] . '.');
            go_to('index.php');
        } else {
            $_SESSION['fails'] = $_SESSION['fails'] + 1;
            if ($_SESSION['fails'] >= 5) {
                $_SESSION['locked_until'] = time() + 60;
                $_SESSION['fails'] = 0;
            }
            $error = 'Invalid username or password.';
        }
    }
}

$page_title = 'Login';
$active = '';
require __DIR__ . '/../includes/header.php';
?>
<div class="login-wrap">
  <div class="card"><div class="card-body p-4">
    <h1 class="h4 mb-1">Agriculture Information System</h1>
    <p class="text-muted small mb-4">Cebu Technological University &middot; AP3 Midterm Project</p>
    <?php if ($error != '') { ?>
      <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
    <?php } ?>
    <form method="post">
      <?php echo csrf_field(); ?>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input class="form-control" id="username" name="username" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" required>
      </div>
      <button class="btn btn-primary w-100">Log in</button>
    </form>
  </div></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
