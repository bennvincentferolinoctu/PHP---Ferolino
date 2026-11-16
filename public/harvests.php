<?php
require __DIR__ . '/../includes/init.php';
require_login();

$search = trim(isset($_GET['q']) ? $_GET['q'] : '');
$grade  = isset($_GET['grade']) ? $_GET['grade'] : '';
$remark = isset($_GET['remark']) ? $_GET['remark'] : '';
$crop   = isset($_GET['crop']) ? $_GET['crop'] : '';
$from   = isset($_GET['from']) ? $_GET['from'] : '';
$to     = isset($_GET['to']) ? $_GET['to'] : '';

$where  = '1=1';
$params = array();
if ($search != '') {
    $where .= ' and (f.farm_name ilike ? or c.crop_name ilike ? or c.variety ilike ? or r.remark_description ilike ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($grade != '')  { $where .= ' and t.grade_id = ?';  $params[] = (int)$grade; }
if ($remark != '') { $where .= ' and t.remark_id = ?'; $params[] = (int)$remark; }
if ($crop != '')   { $where .= ' and p.crop_id = ?';   $params[] = (int)$crop; }
if (valid_date($from)) { $where .= ' and t.harvest_date >= ?'; $params[] = $from; }
if (valid_date($to))   { $where .= ' and t.harvest_date <= ?'; $params[] = $to; }

$sort_columns = array(
    'id' => 't.harvest_id', 'planting' => 't.planting_id', 'farm' => 'f.farm_name', 'crop' => 'c.crop_name',
    'harvested' => 't.harvest_date', 'qty' => 't.quantity_kg', 'price' => 't.selling_price_per_kg',
    'value' => 'estimated_value', 'grade' => 'g.grade_name', 'remark' => 'r.remark_description'
);
list($sort, $dir) = get_sort($sort_columns, 'id');

$per_page = 10;
$from_sql = 'from harvest t
             join planting p on p.planting_id = t.planting_id
             join farm f on f.farm_id = p.farm_id
             join crop c on c.crop_id = p.crop_id
             join quality_grade g on g.grade_id = t.grade_id
             join harvest_remark r on r.remark_id = t.remark_id';
$total  = get_value('select count(*) ' . $from_sql . ' where ' . $where, $params);
$pages  = max(1, ceil($total / $per_page));
$page   = min(get_page(), $pages);
$offset = ($page - 1) * $per_page;

// estimated_value is calculated here: quantity x price (it is not stored in the database)
$sql = 'select t.*, f.farm_name, c.crop_name, g.grade_name, r.remark_description,
               round(t.quantity_kg * t.selling_price_per_kg, 2) as estimated_value '
     . $from_sql . ' where ' . $where
     . ' order by ' . $sort_columns[$sort] . ' ' . $dir . ', t.harvest_id desc'
     . ' limit ' . (int)$per_page . ' offset ' . (int)$offset;
$harvests = get_rows($sql, $params);

$page_title = 'Harvest Records';
$active = 'harvests';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Harvest Records</h1>
  <a class="btn btn-primary" href="harvest_form.php">+ Add Harvest record</a>
</div>

<div class="card mb-3"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-12 col-md-4 col-lg"><label class="form-label small mb-1">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="Search..."></div>
    <?php
    filter_select('grade', 'Quality grade', options_grade(), $grade);
    filter_select('remark', 'Remarks', options_remark(), $remark);
    filter_select('crop', 'Crop', options_crop(), $crop);
    ?>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">Harvest date from</label>
      <input type="date" class="form-control form-control-sm" name="from" value="<?php echo e($from); ?>"></div>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">to</label>
      <input type="date" class="form-control form-control-sm" name="to" value="<?php echo e($to); ?>"></div>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Apply</button>
      <a class="btn btn-light btn-sm" href="harvests.php">Reset</a>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead>
        <tr>
          <th><?php echo sort_link('ID', 'id', $sort, $dir); ?></th>
          <th><?php echo sort_link('Planting', 'planting', $sort, $dir); ?></th>
          <th><?php echo sort_link('Farm', 'farm', $sort, $dir); ?></th>
          <th><?php echo sort_link('Crop', 'crop', $sort, $dir); ?></th>
          <th><?php echo sort_link('Harvested', 'harvested', $sort, $dir); ?></th>
          <th><?php echo sort_link('Qty (kg)', 'qty', $sort, $dir); ?></th>
          <th><?php echo sort_link('Price/kg', 'price', $sort, $dir); ?></th>
          <th><?php echo sort_link('Est. value', 'value', $sort, $dir); ?></th>
          <th><?php echo sort_link('Grade', 'grade', $sort, $dir); ?></th>
          <th><?php echo sort_link('Remarks', 'remark', $sort, $dir); ?></th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($harvests as $h) { ?>
        <tr>
          <td><?php echo $h['harvest_id']; ?></td>
          <td><?php echo $h['planting_id']; ?></td>
          <td><?php echo e($h['farm_name']); ?></td>
          <td><?php echo e($h['crop_name']); ?></td>
          <td><?php echo show_date($h['harvest_date']); ?></td>
          <td><?php echo show_number($h['quantity_kg']); ?></td>
          <td><?php echo show_money($h['selling_price_per_kg']); ?></td>
          <td><?php echo show_money($h['estimated_value']); ?></td>
          <td><?php echo badge($h['grade_name']); ?></td>
          <td><?php echo e($h['remark_description']); ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="harvest_view.php?id=<?php echo $h['harvest_id']; ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="harvest_form.php?id=<?php echo $h['harvest_id']; ?>">Edit</a>
            <?php echo delete_button('harvest_delete.php?id=' . $h['harvest_id'], 'harvest #' . $h['harvest_id'] . ' (' . $h['farm_name'] . ', ' . $h['crop_name'] . ')'); ?>
          </td>
        </tr>
      <?php } ?>
      <?php if (count($harvests) == 0) { ?>
        <tr><td colspan="11" class="text-center text-muted py-4">No records found.</td></tr>
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
