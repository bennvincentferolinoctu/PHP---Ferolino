<?php
require __DIR__ . '/../includes/init.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$f = get_row("select t.*, fr.first_name || ' ' || fr.last_name as farmer_name, st.soil_type_name, it.irrigation_type_name,
                     cu.username as created_name, uu.username as updated_name
              from farm t
              join farmer fr on fr.farmer_id = t.farmer_id
              join soil_type st on st.soil_type_id = t.soil_type_id
              join irrigation_type it on it.irrigation_type_id = t.irrigation_type_id
              left join app_user cu on cu.user_id = t.created_by
              left join app_user uu on uu.user_id = t.updated_by
              where t.farm_id = ?", array($id));
if (!$f) {
    set_message('warning', 'That farm does not exist.');
    go_to('farms.php');
}

$page_title = 'View Farm';
$active = 'farms';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?php echo e($f['farm_name']); ?></h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="farms.php">Back to list</a>
    <a class="btn btn-primary btn-sm" href="farm_form.php?id=<?php echo $id; ?>">Edit</a>
  </div>
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
  <dt class="col-sm-4 col-lg-3">ID</dt><dd class="col-sm-8 col-lg-9"><?php echo $f['farm_id']; ?></dd>
  <dt class="col-sm-4 col-lg-3">Farm name</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['farm_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Farmer</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['farmer_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Location</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['location']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Area (hectares)</dt><dd class="col-sm-8 col-lg-9"><?php echo show_number($f['area_hectares']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Soil type</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['soil_type_name']); ?></dd>
  <dt class="col-sm-4 col-lg-3">Irrigation type</dt><dd class="col-sm-8 col-lg-9"><?php echo e($f['irrigation_type_name']); ?></dd>
</dl></div></div>
<div class="card mt-3"><div class="card-body small text-muted"><div class="row">
  <div class="col-md-6">Created <?php echo show_datetime($f['created_at']); ?> by <?php echo e($f['created_name'] ? $f['created_name'] : '-'); ?></div>
  <div class="col-md-6">Last updated <?php echo show_datetime($f['updated_at']); ?> by <?php echo e($f['updated_name'] ? $f['updated_name'] : '-'); ?></div>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
