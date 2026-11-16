<?php
$menu = array(
    'dashboard' => array('index.php', 'Dashboard'),
    'farmers'   => array('farmers.php', 'Farmers'),
    'farms'     => array('farms.php', 'Farms'),
    'crops'     => array('crops.php', 'Crops'),
    'plantings' => array('plantings.php', 'Planting'),
    'harvests'  => array('harvests.php', 'Harvest'),
    'reports'   => array('reports.php', 'Reports')
);
if (is_admin()) {
    $menu['users'] = array('users.php', 'Users');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($page_title); ?> | Agriculture Information System</title>
  <link href="assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="assets/app.css" rel="stylesheet">
</head>
<body>
<?php if (isset($_SESSION['user'])) { ?>
<nav class="navbar navbar-expand-lg navbar-dark app-nav sticky-top no-print">
  <div class="container-xl">
    <a class="navbar-brand fw-semibold" href="index.php">FarmFero</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($menu as $key => $item) { ?>
          <li class="nav-item">
            <a class="nav-link<?php if ($active == $key) { echo ' active'; } ?>" href="<?php echo $item[0]; ?>"><?php echo $item[1]; ?></a>
          </li>
        <?php } ?>
      </ul>
      <div class="d-flex align-items-center gap-2 text-white-50 small">
        <span><?php echo e($_SESSION['user']['full_name']); ?> <span class="badge text-bg-light"><?php echo e($_SESSION['user']['role_name']); ?></span></span>
        <form method="post" action="logout.php" class="m-0"><?php echo csrf_field(); ?><button class="btn btn-sm btn-outline-light">Logout</button></form>
      </div>
    </div>
  </div>
</nav>
<?php } ?>
<main class="container-xl py-4">
<?php show_messages(); ?>
