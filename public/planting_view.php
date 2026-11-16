<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$p = get_row("select t.*, f.farm_name, fr.first_name || ' ' || fr.last_name as farmer_name, c.crop_name, c.variety, ps.status_name,
                     cu.username as created_name, uu.username as updated_name,
                     (select count(*) from harvest h where h.planting_id = t.planting_id) as harvest_count
              from planting t
              join farm f on f.farm_id = t.farm_id
              join farmer fr on fr.farmer_id = f.farmer_id
              join crop c on c.crop_id = t.crop_id
              join planting_status ps on ps.status_id = t.status_id
              left join app_user cu on cu.user_id = t.created_by
              left join app_user uu on uu.user_id = t.updated_by
              where t.planting_id = ?", array($id));
if (!$p) {
    set_message('warning', 'That planting record does not exist.');
    go_to('plantings.php');
}

$page_title = 'View Planting record';
$active = 'plantings';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Planting #<?php echo $p['planting_id']; ?> (<?php echo e($p['farm_name'] . ', ' . $p['crop_name']); ?>)</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="plantings.php">Back to list</a>
    <a class="btn btn-primary btn-sm" href="planting_form.php?id=<?php echo $id; ?>">Edit</a>
  </div>
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-4 col-lg-3">ID</dt><dd class="col-sm-8 col-lg-9"><?php echo $p['planting_id']; ?></dd>
  <dt class="col-sm-4 col-lg-3">Farm</dt><dd class="col-sm-8 col-lg-9"><?php echo e($p['farm_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Farmer</dt><dd class="col-sm-8 col-lg-9"><?php echo e($p['farmer_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Crop</dt><dd class="col-sm-8 col-lg-9"><?php echo e($p['crop_name'] . ' - ' . $p['variety']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Planting date</dt><dd class="col-sm-8 col-lg-9"><?php echo show_date($p['planting_date']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Area planted (ha)</dt><dd class="col-sm-8 col-lg-9"><?php echo show_number($p['area_planted']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Seed quantity</dt><dd class="col-sm-8 col-lg-9"><?php echo show_number($p['seed_quantity']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Expected harvest</dt><dd class="col-sm-8 col-lg-9"><?php echo show_date($p['expected_harvest_date']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Status</dt><dd class="col-sm-8 col-lg-9"><?php echo badge($p['status_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Harvest records</dt><dd class="col-sm-8 col-lg-9"><?php echo $p['harvest_count']; ?></dd>
</dl></div></div>
<div class="card mt-3"><div class="card-body small text-muted"><div class="row">
  <div class="col-md-6">Created <?php echo show_datetime($p['created_at']); ?> by <?php echo e($p['created_name'] ? $p['created_name'] : '-'); ?></div>
  <div class="col-md-6">Last updated <?php echo show_datetime($p['updated_at']); ?> by <?php echo e($p['updated_name'] ? $p['updated_name'] : '-'); ?></div>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
