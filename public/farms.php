<?php
require __DIR__ . '/../includes/init.php';
require_login();

//1. Search / filter values
$search     = trim(isset($_GET['q']) ? $_GET['q'] : '');
$irrigation = isset($_GET['irrigation']) ? $_GET['irrigation'] : '';
$soil       = isset($_GET['soil']) ? $_GET['soil'] : '';
$farmer     = isset($_GET['farmer']) ? $_GET['farmer'] : '';

//WHERE part
$where  = '1=1';
$params = array();
if ($search != '') {
    $where .= " and (t.farm_name ilike ? or t.location ilike ? or fr.first_name || ' ' || fr.last_name ilike ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($irrigation != '') { $where .= ' and t.irrigation_type_id = ?'; $params[] = (int)$irrigation; }
if ($soil != '')       { $where .= ' and t.soil_type_id = ?';       $params[] = (int)$soil; }
if ($farmer != '')     { $where .= ' and t.farmer_id = ?';          $params[] = (int)$farmer; }

//Sorting
$sort_columns = array(
    'id' => 't.farm_id', 'farm' => 't.farm_name', 'farmer' => 'farmer_name', 'location' => 't.location',
    'area' => 't.area_hectares', 'soil' => 'st.soil_type_name', 'irrigation' => 'it.irrigation_type_name'
);
list($sort, $dir) = get_sort($sort_columns, 'id');

//Pagination
$per_page = 10;
$from_sql = 'from farm t
             join farmer fr on fr.farmer_id = t.farmer_id
             join soil_type st on st.soil_type_id = t.soil_type_id
             join irrigation_type it on it.irrigation_type_id = t.irrigation_type_id';
$total  = get_value('select count(*) ' . $from_sql . ' where ' . $where, $params);
$pages  = max(1, ceil($total / $per_page));
$page   = min(get_page(), $pages);
$offset = ($page - 1) * $per_page;

//Rows (JOINs turn ids into names)
$sql = "select t.*, fr.first_name || ' ' || fr.last_name as farmer_name, st.soil_type_name, it.irrigation_type_name "
     . $from_sql . ' where ' . $where
     . ' order by ' . $sort_columns[$sort] . ' ' . $dir . ', t.farm_id desc'
     . ' limit ' . (int)$per_page . ' offset ' . (int)$offset;
$farms = get_rows($sql, $params);

$page_title = 'Farms';
$active = 'farms';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Farms</h1>
  <a class="btn btn-primary" href="farm_form.php">+ Add Farm</a>
</div>

<div class="card mb-3"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-12 col-md-4 col-lg"><label class="form-label small mb-1">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="Search..."></div>
    <?php
    filter_select('irrigation', 'Irrigation type', options_irrigation(), $irrigation);
    filter_select('soil', 'Soil type', options_soil(), $soil);
    filter_select('farmer', 'Farmer', options_farmer(), $farmer);
    ?>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Apply</button>
      <a class="btn btn-light btn-sm" href="farms.php">Reset</a>
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
          <th><?php echo sort_link('Location', 'location', $sort, $dir); ?></th>
          <th><?php echo sort_link('Area (ha)', 'area', $sort, $dir); ?></th>
          <th><?php echo sort_link('Soil', 'soil', $sort, $dir); ?></th>
          <th><?php echo sort_link('Irrigation', 'irrigation', $sort, $dir); ?></th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($farms as $f) { ?>
        <tr>
          <td><?php echo $f['farm_id']; ?></td>
          <td><?php echo e($f['farm_name']); ?></td>
          <td><?php echo e($f['farmer_name']); ?></td>
          <td><?php echo e($f['location']); ?></td>
          <td><?php echo show_number($f['area_hectares']); ?></td>
          <td><?php echo e($f['soil_type_name']); ?></td>
          <td><?php echo e($f['irrigation_type_name']); ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="farm_view.php?id=<?php echo $f['farm_id']; ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="farm_form.php?id=<?php echo $f['farm_id']; ?>">Edit</a>
            <?php echo delete_button('farm_delete.php?id=' . $f['farm_id'], $f['farm_name']); ?>
          </td>
        </tr>
      <?php } ?>
      <?php if (count($farms) == 0) { ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No records found.</td></tr>
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
