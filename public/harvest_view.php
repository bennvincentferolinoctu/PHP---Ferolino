<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$h = get_row("select t.*, p.planting_date, f.farm_name, fr.first_name || ' ' || fr.last_name as farmer_name,
                     c.crop_name, c.variety, g.grade_name, r.remark_description,
                     round(t.quantity_kg * t.selling_price_per_kg, 2) as estimated_value,
                     cu.username as created_name, uu.username as updated_name
              from harvest t
              join planting p on p.planting_id = t.planting_id
              join farm f on f.farm_id = p.farm_id
              join farmer fr on fr.farmer_id = f.farmer_id
              join crop c on c.crop_id = p.crop_id
              join quality_grade g on g.grade_id = t.grade_id
              join harvest_remark r on r.remark_id = t.remark_id
              left join app_user cu on cu.user_id = t.created_by
              left join app_user uu on uu.user_id = t.updated_by
              where t.harvest_id = ?", array($id));
if (!$h) {
    set_message('warning', 'That harvest record does not exist.');
    go_to('harvests.php');
}

$page_title = 'View Harvest record';
$active = 'harvests';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Harvest #<?php echo $h['harvest_id']; ?> (<?php echo e($h['farm_name'] . ', ' . $h['crop_name']); ?>)</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="harvests.php">Back to list</a>
    <a class="btn btn-primary btn-sm" href="harvest_form.php?id=<?php echo $id; ?>">Edit</a>
  </div>
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-4 col-lg-3">ID</dt><dd class="col-sm-8 col-lg-9"><?php echo $h['harvest_id']; ?></dd>
  <dt class="col-sm-4 col-lg-3">Planting record</dt><dd class="col-sm-8 col-lg-9">#<?php echo $h['planting_id']; ?> (planted <?php echo show_date($h['planting_date']); ?>)</dd>
  <dt class="col-sm-4 col-lg-3">Farm</dt><dd class="col-sm-8 col-lg-9"><?php echo e($h['farm_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Farmer</dt><dd class="col-sm-8 col-lg-9"><?php echo e($h['farmer_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Crop</dt><dd class="col-sm-8 col-lg-9"><?php echo e($h['crop_name'] . ' - ' . $h['variety']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Harvest date</dt><dd class="col-sm-8 col-lg-9"><?php echo show_date($h['harvest_date']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Quantity (kg)</dt><dd class="col-sm-8 col-lg-9"><?php echo show_number($h['quantity_kg']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Selling price per kg</dt><dd class="col-sm-8 col-lg-9"><?php echo show_money($h['selling_price_per_kg']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Estimated value</dt><dd class="col-sm-8 col-lg-9"><?php echo show_money($h['estimated_value']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Quality grade</dt><dd class="col-sm-8 col-lg-9"><?php echo badge($h['grade_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Remarks</dt><dd class="col-sm-8 col-lg-9"><?php echo e($h['remark_description']); ?></dd>
</dl></div></div>
<div class="card mt-3"><div class="card-body small text-muted"><div class="row">
  <div class="col-md-6">Created <?php echo show_datetime($h['created_at']); ?> by <?php echo e($h['created_name'] ? $h['created_name'] : '-'); ?></div>
  <div class="col-md-6">Last updated <?php echo show_datetime($h['updated_at']); ?> by <?php echo e($h['updated_name'] ? $h['updated_name'] : '-'); ?></div>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
