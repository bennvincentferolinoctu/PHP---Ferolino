<?php
require __DIR__ . '/../includes/init.php';
require_login();

// ----- Numbers for the summary cards -----
$total_farmers   = get_value('select count(*) from farmer');
$total_farms     = get_value('select count(*) from farm');
$total_crops     = get_value('select count(*) from crop');
$total_plantings = get_value('select count(*) from planting');
$total_harvests  = get_value('select count(*) from harvest');
$total_kg        = get_value('select coalesce(sum(quantity_kg), 0) from harvest');
$total_value     = get_value('select coalesce(sum(quantity_kg * selling_price_per_kg), 0) from harvest');

// ----- Data for the status table -----
$by_status = get_rows('select s.status_name, count(p.planting_id) as total
                       from planting_status s
                       left join planting p on p.status_id = s.status_id
                       group by s.status_id, s.status_name
                       order by s.status_id');

// ----- Latest records -----
$recent_plantings = get_rows('select f.farm_name, c.crop_name, p.planting_date, s.status_name
                              from planting p
                              join farm f on f.farm_id = p.farm_id
                              join crop c on c.crop_id = p.crop_id
                              join planting_status s on s.status_id = p.status_id
                              order by p.planting_date desc, p.planting_id desc
                              limit 5');

$recent_harvests = get_rows('select f.farm_name, c.crop_name, h.harvest_date, h.quantity_kg, g.grade_name
                             from harvest h
                             join planting p on p.planting_id = h.planting_id
                             join farm f on f.farm_id = p.farm_id
                             join crop c on c.crop_id = p.crop_id
                             join quality_grade g on g.grade_id = h.grade_id
                             order by h.harvest_date desc, h.harvest_id desc
                             limit 5');

$page_title = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl"><a class="card stat-card h-100" href="farmers.php"><div class="card-body">
    <div class="text-muted small">Farmers</div><div class="num"><?php echo $total_farmers; ?></div></div></a></div>
  <div class="col-6 col-md-4 col-xl"><a class="card stat-card h-100" href="farms.php"><div class="card-body">
    <div class="text-muted small">Farms</div><div class="num"><?php echo $total_farms; ?></div></div></a></div>
  <div class="col-6 col-md-4 col-xl"><a class="card stat-card h-100" href="crops.php"><div class="card-body">
    <div class="text-muted small">Crops</div><div class="num"><?php echo $total_crops; ?></div></div></a></div>
  <div class="col-6 col-md-4 col-xl"><a class="card stat-card h-100" href="plantings.php"><div class="card-body">
    <div class="text-muted small">Planting records</div><div class="num"><?php echo $total_plantings; ?></div></div></a></div>
  <div class="col-6 col-md-4 col-xl"><a class="card stat-card h-100" href="harvests.php"><div class="card-body">
    <div class="text-muted small">Harvest records</div><div class="num"><?php echo $total_harvests; ?></div></div></a></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="card h-100"><div class="card-body">
    <div class="text-muted small">Total harvested quantity</div>
    <div class="num h2 mb-0"><?php echo show_number($total_kg); ?> <small class="fs-6 text-muted">kg</small></div>
  </div></div></div>
  <div class="col-md-6"><div class="card h-100"><div class="card-body">
    <div class="text-muted small">Estimated harvest value</div>
    <div class="num h2 mb-0"><?php echo show_money($total_value); ?></div>
  </div></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-5"><div class="card h-100"><div class="card-body">
    <h2 class="h6">Plantings by status</h2>
    <table class="table table-sm mb-0"><tbody>
      <?php foreach ($by_status as $row) { ?>
        <tr><td><?php echo badge($row['status_name']); ?></td><td class="text-end"><?php echo $row['total']; ?></td></tr>
      <?php } ?>
    </tbody></table>
  </div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-body">
    <div class="d-flex justify-content-between"><h2 class="h6">Recent planting records</h2><a href="plantings.php" class="small">View all</a></div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
      <thead><tr><th>Farm</th><th>Crop</th><th>Planted</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($recent_plantings as $row) { ?>
        <tr>
          <td><?php echo e($row['farm_name']); ?></td>
          <td><?php echo e($row['crop_name']); ?></td>
          <td><?php echo show_date($row['planting_date']); ?></td>
          <td><?php echo badge($row['status_name']); ?></td>
        </tr>
      <?php } ?>
      <?php if (count($recent_plantings) == 0) { ?><tr><td colspan="4" class="text-muted">No planting records yet.</td></tr><?php } ?>
      </tbody>
    </table></div>
  </div></div></div>

  <div class="col-lg-6"><div class="card"><div class="card-body">
    <div class="d-flex justify-content-between"><h2 class="h6">Recent harvest records</h2><a href="harvests.php" class="small">View all</a></div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
      <thead><tr><th>Farm</th><th>Crop</th><th>Date</th><th class="text-end">Kg</th><th>Grade</th></tr></thead>
      <tbody>
      <?php foreach ($recent_harvests as $row) { ?>
        <tr>
          <td><?php echo e($row['farm_name']); ?></td>
          <td><?php echo e($row['crop_name']); ?></td>
          <td><?php echo show_date($row['harvest_date']); ?></td>
          <td class="text-end"><?php echo show_number($row['quantity_kg']); ?></td>
          <td><?php echo badge($row['grade_name']); ?></td>
        </tr>
      <?php } ?>
      <?php if (count($recent_harvests) == 0) { ?><tr><td colspan="5" class="text-muted">No harvest records yet.</td></tr><?php } ?>
      </tbody>
    </table></div>
  </div></div></div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
