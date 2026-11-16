<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$c = get_row('select t.*, cc.crop_category_name, s.season_name, cu.username as created_name, uu.username as updated_name
              from crop t
              join crop_category cc on cc.crop_category_id = t.crop_category_id
              join season s on s.season_id = t.season_id
              left join app_user cu on cu.user_id = t.created_by
              left join app_user uu on uu.user_id = t.updated_by
              where t.crop_id = ?', array($id));
if (!$c) {
    set_message('warning', 'That crop does not exist.');
    go_to('crops.php');
}

$page_title = 'View Crop';
$active = 'crops';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($c['crop_name'] . ' (' . $c['variety'] . ')'); ?></h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="crops.php">Back to list</a>
    <a class="btn btn-primary btn-sm" href="crop_form.php?id=<?php echo $id; ?>">Edit</a>
  </div>
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-4 col-lg-3">ID</dt><dd class="col-sm-8 col-lg-9"><?php echo $c['crop_id']; ?></dd>
  <dt class="col-sm-4 col-lg-3">Crop name</dt><dd class="col-sm-8 col-lg-9"><?php echo e($c['crop_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Variety</dt><dd class="col-sm-8 col-lg-9"><?php echo e($c['variety']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Category</dt><dd class="col-sm-8 col-lg-9"><?php echo e($c['crop_category_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Growing period</dt><dd class="col-sm-8 col-lg-9"><?php echo $c['growing_period_days']; ?> days</dd>
  <dt class="col-sm-4 col-lg-3">Recommended season</dt><dd class="col-sm-8 col-lg-9"><?php echo e($c['season_name']); ?></dd>
</dl></div></div>
<div class="card mt-3"><div class="card-body small text-muted"><div class="row">
  <div class="col-md-6">Created <?php echo show_datetime($c['created_at']); ?> by <?php echo e($c['created_name'] ? $c['created_name'] : '-'); ?></div>
  <div class="col-md-6">Last updated <?php echo show_datetime($c['updated_at']); ?> by <?php echo e($c['updated_name'] ? $c['updated_name'] : '-'); ?></div>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
