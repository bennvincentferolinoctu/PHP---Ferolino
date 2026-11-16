<?php
require __DIR__ . '/../includes/init.php';
require_login();

// ----- Which report? -----
$report_names = array(
    'farmers_farms'  => 'Farmers and their farms',
    'crops_per_farm' => 'Crops planted per farm',
    'planting_status'=> 'Planting records by status',
    'harvest_records'=> 'Harvest records',
    'total_quantity' => 'Total harvested quantity',
    'harvest_value'  => 'Estimated harvest value'
);
$r = 'farmers_farms';
if (isset($_GET['r']) && isset($report_names[$_GET['r']])) {
    $r = $_GET['r'];
}

// ----- Date range and dropdown filters from the URL -----
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to   = isset($_GET['to']) ? $_GET['to'] : '';
$date_error = '';
if ($from != '' && !valid_date($from)) { $date_error = 'The "from" date is not valid.'; $from = ''; }
if ($to != '' && !valid_date($to))     { $date_error = 'The "to" date is not valid.';   $to = ''; }
if ($from != '' && $to != '' && $from > $to) {
    $date_error = 'The "from" date must not be after the "to" date.';
    $from = '';
    $to = '';
}

$f_farmer = isset($_GET['farmer']) ? $_GET['farmer'] : '';
$f_farm   = isset($_GET['farm']) ? $_GET['farm'] : '';
$f_crop   = isset($_GET['crop']) ? $_GET['crop'] : '';
$f_status = isset($_GET['status']) ? $_GET['status'] : '';
$f_grade  = isset($_GET['grade']) ? $_GET['grade'] : '';

// ----- Each report: its SQL, columns, date column and filters -----
// $columns = list of array(field name, heading, type, add up in a total row?)
$params = array();
$filters = array();      
$date_label = '';
$group_by_status = false;

if ($r == 'farmers_farms') {
    $date_label = 'Date registered';
    $where = '1=1';
    if ($from != '') { $where .= ' and fr.date_registered >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and fr.date_registered <= ?'; $params[] = $to; }
    if ($f_farmer != '') { $where .= ' and fr.farmer_id = ?'; $params[] = (int)$f_farmer; }
    $filters = array('farmer' => array('Farmer', options_farmer(), $f_farmer));
    $sql = "select fr.last_name || ', ' || fr.first_name as farmer, fr.address, f.farm_name, f.location,
                   f.area_hectares, st.soil_type_name as soil, it.irrigation_type_name as irrigation
            from farmer fr
            left join farm f on f.farmer_id = fr.farmer_id
            left join soil_type st on st.soil_type_id = f.soil_type_id
            left join irrigation_type it on it.irrigation_type_id = f.irrigation_type_id
            where $where
            order by fr.last_name, fr.first_name, f.farm_name";
    $columns = array(
        array('farmer', 'Farmer', 'text', false), array('address', 'Address', 'text', false),
        array('farm_name', 'Farm', 'text', false), array('location', 'Location', 'text', false),
        array('area_hectares', 'Area (ha)', 'number', true), array('soil', 'Soil', 'text', false),
        array('irrigation', 'Irrigation', 'text', false)
    );

} elseif ($r == 'crops_per_farm') {
    $date_label = 'Planting date';
    $where = '1=1';
    if ($from != '') { $where .= ' and p.planting_date >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and p.planting_date <= ?'; $params[] = $to; }
    if ($f_farm != '') { $where .= ' and p.farm_id = ?'; $params[] = (int)$f_farm; }
    if ($f_crop != '') { $where .= ' and p.crop_id = ?'; $params[] = (int)$f_crop; }
    $filters = array('farm' => array('Farm', options_farm(), $f_farm), 'crop' => array('Crop', options_crop(), $f_crop));
    $sql = "select f.farm_name, fr.first_name || ' ' || fr.last_name as farmer, c.crop_name, c.variety,
                   count(*) as plantings, sum(p.area_planted) as area_planted
            from planting p
            join farm f on f.farm_id = p.farm_id
            join farmer fr on fr.farmer_id = f.farmer_id
            join crop c on c.crop_id = p.crop_id
            where $where
            group by f.farm_id, f.farm_name, fr.first_name, fr.last_name, c.crop_id, c.crop_name, c.variety
            order by f.farm_name, c.crop_name";
    $columns = array(
        array('farm_name', 'Farm', 'text', false), array('farmer', 'Farmer', 'text', false),
        array('crop_name', 'Crop', 'text', false), array('variety', 'Variety', 'text', false),
        array('plantings', 'Plantings', 'int', true), array('area_planted', 'Area planted (ha)', 'number', true)
    );

} elseif ($r == 'planting_status') {
    $date_label = 'Planting date';
    $group_by_status = true;
    $where = '1=1';
    if ($from != '') { $where .= ' and p.planting_date >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and p.planting_date <= ?'; $params[] = $to; }
    if ($f_status != '') { $where .= ' and p.status_id = ?'; $params[] = (int)$f_status; }
    $filters = array('status' => array('Status', options_status(), $f_status));
    $sql = "select ps.status_name as status, p.planting_id, f.farm_name, c.crop_name, p.planting_date,
                   p.expected_harvest_date, p.area_planted
            from planting p
            join planting_status ps on ps.status_id = p.status_id
            join farm f on f.farm_id = p.farm_id
            join crop c on c.crop_id = p.crop_id
            where $where
            order by ps.status_id, p.planting_date";
    $columns = array(
        array('status', 'Status', 'badge', false), array('planting_id', 'Planting #', 'text', false),
        array('farm_name', 'Farm', 'text', false), array('crop_name', 'Crop', 'text', false),
        array('planting_date', 'Planted', 'date', false), array('expected_harvest_date', 'Expected harvest', 'date', false),
        array('area_planted', 'Area (ha)', 'number', true)
    );

} elseif ($r == 'harvest_records') {
    $date_label = 'Harvest date';
    $where = '1=1';
    if ($from != '') { $where .= ' and h.harvest_date >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and h.harvest_date <= ?'; $params[] = $to; }
    if ($f_grade != '') { $where .= ' and h.grade_id = ?'; $params[] = (int)$f_grade; }
    if ($f_crop != '')  { $where .= ' and p.crop_id = ?';  $params[] = (int)$f_crop; }
    $filters = array('grade' => array('Quality grade', options_grade(), $f_grade), 'crop' => array('Crop', options_crop(), $f_crop));
    $sql = "select h.harvest_id, h.harvest_date, f.farm_name, fr.first_name || ' ' || fr.last_name as farmer, c.crop_name,
                   h.quantity_kg, h.selling_price_per_kg, round(h.quantity_kg * h.selling_price_per_kg, 2) as estimated_value,
                   g.grade_name, rm.remark_description
            from harvest h
            join planting p on p.planting_id = h.planting_id
            join farm f on f.farm_id = p.farm_id
            join farmer fr on fr.farmer_id = f.farmer_id
            join crop c on c.crop_id = p.crop_id
            join quality_grade g on g.grade_id = h.grade_id
            join harvest_remark rm on rm.remark_id = h.remark_id
            where $where
            order by h.harvest_date desc, h.harvest_id desc";
    $columns = array(
        array('harvest_id', 'ID', 'text', false), array('harvest_date', 'Harvested', 'date', false),
        array('farm_name', 'Farm', 'text', false), array('farmer', 'Farmer', 'text', false),
        array('crop_name', 'Crop', 'text', false), array('quantity_kg', 'Qty (kg)', 'number', true),
        array('selling_price_per_kg', 'Price/kg', 'money', false), array('estimated_value', 'Est. value', 'money', true),
        array('grade_name', 'Grade', 'badge', false), array('remark_description', 'Remarks', 'text', false)
    );

} elseif ($r == 'total_quantity') {
    $date_label = 'Harvest date';
    $where = '1=1';
    if ($from != '') { $where .= ' and h.harvest_date >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and h.harvest_date <= ?'; $params[] = $to; }
    if ($f_crop != '')  { $where .= ' and p.crop_id = ?';  $params[] = (int)$f_crop; }
    if ($f_grade != '') { $where .= ' and h.grade_id = ?'; $params[] = (int)$f_grade; }
    $filters = array('crop' => array('Crop', options_crop(), $f_crop), 'grade' => array('Quality grade', options_grade(), $f_grade));
    $sql = "select c.crop_name, c.variety, count(*) as harvests, sum(h.quantity_kg) as total_kg,
                   round(avg(h.quantity_kg), 2) as avg_kg
            from harvest h
            join planting p on p.planting_id = h.planting_id
            join crop c on c.crop_id = p.crop_id
            where $where
            group by c.crop_id, c.crop_name, c.variety
            order by total_kg desc";
    $columns = array(
        array('crop_name', 'Crop', 'text', false), array('variety', 'Variety', 'text', false),
        array('harvests', 'Harvests', 'int', true), array('total_kg', 'Total (kg)', 'number', true),
        array('avg_kg', 'Average per harvest (kg)', 'number', false)
    );

} else {   // harvest_value
    $date_label = 'Harvest date';
    $where = '1=1';
    if ($from != '') { $where .= ' and h.harvest_date >= ?'; $params[] = $from; }
    if ($to != '')   { $where .= ' and h.harvest_date <= ?'; $params[] = $to; }
    if ($f_farm != '') { $where .= ' and p.farm_id = ?'; $params[] = (int)$f_farm; }
    if ($f_crop != '') { $where .= ' and p.crop_id = ?'; $params[] = (int)$f_crop; }
    $filters = array('farm' => array('Farm', options_farm(), $f_farm), 'crop' => array('Crop', options_crop(), $f_crop));
    $sql = "select f.farm_name, c.crop_name, h.harvest_date, h.quantity_kg, h.selling_price_per_kg,
                   round(h.quantity_kg * h.selling_price_per_kg, 2) as estimated_value
            from harvest h
            join planting p on p.planting_id = h.planting_id
            join farm f on f.farm_id = p.farm_id
            join crop c on c.crop_id = p.crop_id
            where $where
            order by h.harvest_date desc, h.harvest_id desc";
    $columns = array(
        array('farm_name', 'Farm', 'text', false), array('crop_name', 'Crop', 'text', false),
        array('harvest_date', 'Harvested', 'date', false), array('quantity_kg', 'Qty (kg)', 'number', true),
        array('selling_price_per_kg', 'Price/kg', 'money', false), array('estimated_value', 'Est. value (qty x price)', 'money', true)
    );
}

$data = get_rows($sql, $params);

// ----- CSV download -----
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $r . '_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    $headings = array();
    foreach ($columns as $col) { $headings[] = $col[1]; }
    fputcsv($out, $headings);
    foreach ($data as $row) {
        $line = array();
        foreach ($columns as $col) { $line[] = $row[$col[0]]; }
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

// ----- Totals for the columns marked true -----
$totals = array();
foreach ($columns as $col) {
    if ($col[3]) {
        $sum = 0;
        foreach ($data as $row) { $sum = $sum + $row[$col[0]]; }
        $totals[$col[0]] = $sum;
    }
}

// Show a value in the right format
function show_cell($value, $type) {
    if ($value === null || $value === '') { return '-'; }
    if ($type == 'date')   { return show_date($value); }
    if ($type == 'money')  { return show_money($value); }
    if ($type == 'number') { return show_number($value); }
    if ($type == 'int')    { return number_format($value); }
    if ($type == 'badge')  { return badge($value); }
    return e($value);
}

$page_title = $report_names[$r] . ' - Reports';
$active = 'reports';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="h3 mb-0">Reports</h1>
  <div class="no-print d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?php echo e(url_with(array('export' => 'csv'))); ?>">Export CSV</a>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
  </div>
</div>

<ul class="nav nav-pills mb-3 no-print flex-wrap gap-1">
  <?php foreach ($report_names as $key => $name) { ?>
    <li class="nav-item">
      <a class="nav-link py-1<?php if ($key == $r) { echo ' active'; } ?>" <?php if ($key == $r) { echo 'style="background:#2d6a4f"'; } ?> href="?r=<?php echo $key; ?>"><?php echo e($name); ?></a>
    </li>
  <?php } ?>
</ul>

<div class="card mb-3 no-print"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <input type="hidden" name="r" value="<?php echo e($r); ?>">
    <div class="col-6 col-md-3 col-lg-2"><label class="form-label small mb-1"><?php echo e($date_label); ?> from</label>
      <input type="date" name="from" class="form-control form-control-sm" value="<?php echo e($from); ?>"></div>
    <div class="col-6 col-md-3 col-lg-2"><label class="form-label small mb-1">to</label>
      <input type="date" name="to" class="form-control form-control-sm" value="<?php echo e($to); ?>"></div>
    <?php foreach ($filters as $name => $f) { filter_select($name, $f[0], $f[1], $f[2]); } ?>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Generate</button>
      <a class="btn btn-light btn-sm" href="?r=<?php echo e($r); ?>">Reset</a>
    </div>
  </form>
  <?php if ($date_error != '') { ?><div class="text-danger small mt-2"><?php echo e($date_error); ?></div><?php } ?>
</div></div>

<div class="card"><div class="card-body">
  <h2 class="h5"><?php echo e($report_names[$r]); ?></h2>
  <p class="text-muted small">Generated <?php echo date('M d, Y h:i A'); ?><?php
    if ($from != '' || $to != '') {
        echo ' &middot; ' . e($date_label) . ': ' . e($from != '' ? $from : 'any') . ' to ' . e($to != '' ? $to : 'any');
    } ?> &middot; <?php echo count($data); ?> row(s)</p>

  <?php
  // Planting-status report: small summary of how many records per status
  if ($group_by_status && count($data) > 0) {
      $counts = array();
      foreach ($data as $row) {
          if (!isset($counts[$row['status']])) { $counts[$row['status']] = 0; }
          $counts[$row['status']] = $counts[$row['status']] + 1;
      }
      echo '<p>';
      foreach ($counts as $status_name => $n) {
          echo badge($status_name) . ' <strong>' . $n . '</strong> &nbsp; ';
      }
      echo '</p>';
  }
  ?>

  <div class="table-responsive"><table class="table table-sm table-striped align-middle">
    <thead><tr>
      <?php foreach ($columns as $col) {
          $right = in_array($col[2], array('number', 'int', 'money')) ? ' class="text-end"' : ''; ?>
        <th<?php echo $right; ?>><?php echo e($col[1]); ?></th>
      <?php } ?>
    </tr></thead>
    <tbody>
    <?php foreach ($data as $row) { ?>
      <tr>
        <?php foreach ($columns as $col) {
            $right = in_array($col[2], array('number', 'int', 'money')) ? ' class="text-end"' : ''; ?>
          <td<?php echo $right; ?>><?php echo show_cell($row[$col[0]], $col[2]); ?></td>
        <?php } ?>
      </tr>
    <?php } ?>
    <?php if (count($data) == 0) { ?>
      <tr><td colspan="<?php echo count($columns); ?>" class="text-center text-muted py-4">No data for the selected criteria.</td></tr>
    <?php } ?>
    </tbody>
    <?php if (count($data) > 0 && count($totals) > 0) { ?>
    <tfoot><tr class="fw-semibold">
      <?php foreach ($columns as $i => $col) { ?>
        <td class="<?php echo isset($totals[$col[0]]) ? 'text-end' : ''; ?>">
          <?php
          if (isset($totals[$col[0]])) { echo show_cell($totals[$col[0]], $col[2]); }
          elseif ($i == 0) { echo 'Total'; }
          ?>
        </td>
      <?php } ?>
    </tr></tfoot>
    <?php } ?>
  </table></div>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
