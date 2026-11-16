<?php
require __DIR__ . '/../includes/init.php';
require_login();

$search = trim(isset($_GET['q']) ? $_GET['q'] : '');
$status = isset($_GET['status']) ? $_GET['status'] : '';
$farm   = isset($_GET['farm']) ? $_GET['farm'] : '';
$crop   = isset($_GET['crop']) ? $_GET['crop'] : '';
$from   = isset($_GET['from']) ? $_GET['from'] : '';
$to     = isset($_GET['to']) ? $_GET['to'] : '';

$where  = '1=1';
$params = array();
if ($search != '') {
    $where .= " and (f.farm_name ilike ? or c.crop_name ilike ? or c.variety ilike ? or fr.first_name || ' ' || fr.last_name ilike ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($status != '') { $where .= ' and t.status_id = ?'; $params[] = (int)$status; }
if ($farm != '')   { $where .= ' and t.farm_id = ?';   $params[] = (int)$farm; }
if ($crop != '')   { $where .= ' and t.crop_id = ?';   $params[] = (int)$crop; }
if (valid_date($from)) { $where .= ' and t.planting_date >= ?'; $params[] = $from; }
if (valid_date($to))   { $where .= ' and t.planting_date <= ?'; $params[] = $to; }

$sort_columns = array(
    'id' => 't.planting_id', 'farm' => 'f.farm_name', 'farmer' => 'farmer_name', 'crop' => 'c.crop_name',
    'planted' => 't.planting_date', 'area' => 't.area_planted', 'seed' => 't.seed_quantity',
    'expected' => 't.expected_harvest_date', 'status' => 'ps.status_name'
);
list($sort, $dir) = get_sort($sort_columns, 'id');

$per_page = 10;
$from_sql = 'from planting t
             join farm f on f.farm_id = t.farm_id
             join farmer fr on fr.farmer_id = f.farmer_id
             join crop c on c.crop_id = t.crop_id
             join planting_status ps on ps.status_id = t.status_id';
$total  = get_value('select count(*) ' . $from_sql . ' where ' . $where, $params);
$pages  = max(1, ceil($total / $per_page));
$page   = min(get_page(), $pages);
$offset = ($page - 1) * $per_page;

$sql = "select t.*, f.farm_name, fr.first_name || ' ' || fr.last_name as farmer_name, c.crop_name, c.variety, ps.status_name "
     . $from_sql . ' where ' . $where
     . ' order by ' . $sort_columns[$sort] . ' ' . $dir . ', t.planting_id desc'
     . ' limit ' . (int)$per_page . ' offset ' . (int)$offset;
$plantings = get_rows($sql, $params);

$page_title = 'Planting Records';
$active = 'plantings';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Planting Records</h1>
  <a class="btn btn-primary" href="planting_form.php">+ Add Planting record</a>
</div>

<div class="card mb-3"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-12 col-md-4 col-lg"><label class="form-label small mb-1">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="Search..."></div>
    <?php
    filter_select('status', 'Status', options_status(), $status);
    filter_select('farm', 'Farm', options_farm(), $farm);
    filter_select('crop', 'Crop', options_crop(), $crop);
    ?>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">Planting date from</label>
      <input type="date" class="form-control form-control-sm" name="from" value="<?php echo e($from); ?>"></div>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">to</label>
      <input type="date" class="form-control form-control-sm" name="to" value="<?php echo e($to); ?>"></div>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Apply</button>
      <a class="btn btn-light btn-sm" href="plantings.php">Reset</a>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead>
        <tr>
          <th><?php echo sort_link('ID', 'id', $sort, $dir); ?></th>
          <th><?php echo sort_link('Farm', 'farm', $sort, $dir); ?></th>
          <th><?php echo sort_link('Farmer', 'farmer', $sort, $dir); ?></th>
          <th><?php echo sort_link('Crop', 'crop', $sort, $dir); ?></th>
          <th><?php echo sort_link('Planted', 'planted', $sort, $dir); ?></th>
          <th><?php echo sort_link('Area (ha)', 'area', $sort, $dir); ?></th>
          <th><?php echo sort_link('Seed qty', 'seed', $sort, $dir); ?></th>
          <th><?php echo sort_link('Expected harvest', 'expected', $sort, $dir); ?></th>
          <th><?php echo sort_link('Status', 'status', $sort, $dir); ?></th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($plantings as $p) { ?>
        <tr>
          <td><?php echo $p['planting_id']; ?></td>
          <td><?php echo e($p['farm_name']); ?></td>
          <td><?php echo e($p['farmer_name']); ?></td>
          <td><?php echo e($p['crop_name']); ?></td>
          <td><?php echo show_date($p['planting_date']); ?></td>
          <td><?php echo show_number($p['area_planted']); ?></td>
          <td><?php echo show_number($p['seed_quantity']); ?></td>
          <td><?php echo show_date($p['expected_harvest_date']); ?></td>
          <td><?php echo badge($p['status_name']); ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="planting_view.php?id=<?php echo $p['planting_id']; ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="planting_form.php?id=<?php echo $p['planting_id']; ?>">Edit</a>
            <?php echo delete_button('planting_delete.php?id=' . $p['planting_id'], 'planting #' . $p['planting_id'] . ' (' . $p['farm_name'] . ', ' . $p['crop_name'] . ')'); ?>
          </td>
        </tr>
      <?php } ?>
      <?php if (count($plantings) == 0) { ?>
        <tr><td colspan="10" class="text-center text-muted py-4">No records found.</td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <div class="small text-muted"><?php echo showing_text($total, $page, $per_page); ?></div>
    <?php show_pager($total, $page, $per_page); ?>
  </div>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
