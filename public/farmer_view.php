<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$f = get_row('select t.*, cu.username as created_name, uu.username as updated_name,
                     (select count(*) from farm x where x.farmer_id = t.farmer_id) as farm_count
              from farmer t
              left join app_user cu on cu.user_id = t.created_by
              left join app_user uu on uu.user_id = t.updated_by
              where t.farmer_id = ?', array($id));
if (!$f) {
    set_message('warning', 'That farmer does not exist.');
    go_to('farmers.php');
}

$page_title = 'View Farmer';
$active = 'farmers';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($f['first_name'] . ' ' . $f['last_name']); ?></h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="farmers.php">Back to list</a>
    <a class="btn btn-primary btn-sm" href="farmer_form.php?id=<?php echo $id; ?>">Edit</a>
  </div>
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-4 col-lg-3">ID</dt><dd class="col-sm-8 col-lg-9"><?php echo $f['farmer_id']; ?></dd>
  <dt class="col-sm-4 col-lg-3">First name</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['first_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Last name</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['last_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Contact number</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['contact_number']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Address</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['address']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Date registered</dt><dd class="col-sm-8 col-lg-9"><?php echo show_date($f['date_registered']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Number of farms</dt><dd class="col-sm-8 col-lg-9"><?php echo $f['farm_count']; ?></dd>
</dl></div></div>
<div class="card mt-3"><div class="card-body small text-muted"><div class="row">
  <div class="col-md-6">Created <?php echo show_datetime($f['created_at']); ?> by <?php echo e($f['created_name'] ? $f['created_name'] : '-'); ?></div>
  <div class="col-md-6">Last updated <?php echo show_datetime($f['updated_at']); ?> by <?php echo e($f['updated_name'] ? $f['updated_name'] : '-'); ?></div>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
